<?php

namespace App\Services;

use App\Models\Automatizacion;
use App\Models\AutomatizacionLog;
use App\Models\CajaSesion;
use App\Models\ColaCorreo;
use App\Models\Cotizacion;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\DevolucionVenta;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Venta;
use App\Support\Formato;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

/**
 * Motor de automatizaciones. La tabla `automatizaciones` guarda la configuración;
 * el comando `ventas:automatizaciones` (cada minuto) ejecuta las que toquen.
 */
class AutomatizacionService
{
    public function __construct(private CajaService $caja)
    {
    }

    /* ======================= Configuración ======================= */

    public function asegurarConfiguracion(?Collection $tiendas = null): void
    {
        $tiendas ??= Tienda::where('estado', 1)->get();

        foreach (AutomatizacionCatalogo::definiciones() as $codigo => $def) {
            if ($def['alcance'] === 'global') {
                $this->crearSiFalta(null, $codigo, $def);
                continue;
            }
            foreach ($tiendas as $tienda) {
                $this->crearSiFalta($tienda->id, $codigo, $def);
            }
        }
    }

    private function crearSiFalta(?int $tiendaId, string $codigo, array $def): Automatizacion
    {
        return Automatizacion::firstOrCreate(
            ['tienda_id' => $tiendaId, 'codigo' => $codigo],
            [
                'nombre' => $def['nombre'],
                'descripcion' => $def['descripcion'],
                'activo' => $def['activo'],
                'frecuencia' => $def['frecuencia'],
                'hora_ejecucion' => $def['hora'] ? $def['hora'] . ':00' : null,
                'parametros' => AutomatizacionCatalogo::valoresPorDefecto($codigo),
            ]
        );
    }

    private function config(string $codigo, ?int $tiendaId): ?Automatizacion
    {
        $def = AutomatizacionCatalogo::definiciones()[$codigo] ?? null;
        if (! $def) {
            return null;
        }
        $tiendaId = $def['alcance'] === 'global' ? null : $tiendaId;

        return Automatizacion::where('codigo', $codigo)->where('tienda_id', $tiendaId)->first()
            ?? $this->crearSiFalta($tiendaId, $codigo, $def);
    }

    /* ======================= Ejecución ======================= */

    /** Ejecuta las automatizaciones que ya les toca. @return int cantidad ejecutada */
    public function ejecutarPendientes(): int
    {
        $this->asegurarConfiguracion();

        $n = 0;
        foreach (Automatizacion::where('activo', true)->get() as $a) {
            if ($this->estaPendiente($a)) {
                $this->ejecutar($a);
                $n++;
            }
        }

        return $n;
    }

    public function estaPendiente(Automatizacion $a): bool
    {
        if (! $a->activo || $a->frecuencia === 'evento') {
            return false;
        }

        $ahora = now();
        if ($a->frecuencia === 'cada_hora') {
            return ! $a->ultima_ejecucion_at || $a->ultima_ejecucion_at->lte($ahora->copy()->subHour());
        }

        $programada = $ahora->copy()->setTimeFromTimeString($a->hora_ejecucion ?: '00:00:00');

        return $ahora->gte($programada) && (! $a->ultima_ejecucion_at || $a->ultima_ejecucion_at->lt($programada));
    }

    public function ejecutar(Automatizacion $a): AutomatizacionLog
    {
        $log = AutomatizacionLog::create([
            'automatizacion_id' => $a->id,
            'tienda_id' => $a->tienda_id,
            'estado' => 'exitoso',
            'iniciada_at' => now(),
        ]);

        try {
            $metodo = 'run' . Str::studly($a->codigo);
            if (! method_exists($this, $metodo)) {
                throw new \RuntimeException("La automatización {$a->codigo} no se ejecuta manualmente.");
            }
            $res = $this->{$metodo}($a);
            $estado = $res['estado'] ?? 'exitoso';
        } catch (\Throwable $e) {
            $estado = 'error';
            $res = ['mensaje' => Str::limit($e->getMessage(), 250, ''), 'detalle' => ['en' => basename($e->getFile()) . ':' . $e->getLine()]];
        }

        $log->update([
            'estado' => $estado,
            'mensaje' => $res['mensaje'] ?? null,
            'detalle' => $res['detalle'] ?? null,
            'finalizada_at' => now(),
        ]);
        $a->update(['ultima_ejecucion_at' => now()]);

        return $log;
    }

    /* ======================= Disparadores por evento ======================= */

    public function encolarComprobanteVenta(Venta $venta): void
    {
        try {
            $a = $this->config('comprobante_venta', $venta->tienda_id);
            $venta->loadMissing(['cliente', 'tienda', 'detalles.producto', 'vendedor']);

            if (! $a || ! $a->activo || ! $venta->cliente || ! $venta->cliente->email) {
                return;
            }

            $this->encolar($venta->cliente->email, "Comprobante de venta {$venta->numero}", 'comprobante_venta', [
                'empresa' => $venta->tienda->empresa->nombre ?? $venta->tienda->nombre,
                'tienda' => $venta->tienda->nombre,
                'numero' => $venta->numero,
                'fecha' => $venta->fecha->format('d/m/Y H:i'),
                'cliente' => $venta->cliente->nombre,
                'tipo_pago' => $venta->tipo_pago,
                'subtotal' => Formato::dinero($venta->subtotal),
                'descuento' => Formato::dinero($venta->descuento),
                'total' => Formato::dinero($venta->total),
                'pagado' => Formato::dinero($venta->pagado),
                'saldo' => Formato::dinero($venta->saldo),
                'items' => $venta->detalles->map(fn ($d) => [
                    'producto' => $d->producto->nombre,
                    'cantidad' => Formato::cantidad($d->cantidad),
                    'precio' => Formato::dinero($d->precio_unitario),
                    'subtotal' => Formato::dinero($d->subtotal),
                ])->all(),
            ], $venta->tienda_id, $a->id);
        } catch (\Throwable $e) {
            report($e); // un fallo de correo nunca debe romper la venta
        }
    }

    public function encolarBienvenida(User $usuario, ?string $passwordTemporal = null): void
    {
        try {
            $a = $this->config('bienvenida_usuario', null);
            if (! $a || ! $a->activo || ! $usuario->email) {
                return;
            }
            $this->encolar($usuario->email, 'Bienvenido al sistema', 'bienvenida', [
                'nombre' => $usuario->nombre_completo ?? $usuario->email,
                'email' => $usuario->email,
                'password' => $passwordTemporal,
                'url' => url('/'),
            ], null, $a->id);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /* ======================= Handlers programados ======================= */

    private function runCierreCajaAutomatico(Automatizacion $a): array
    {
        $sesiones = CajaSesion::with(['caja', 'usuarioApertura'])
            ->where('estado', 'abierta')
            ->whereHas('caja', fn ($q) => $q->where('tienda_id', $a->tienda_id))
            ->when($a->parametro('solo_dias_anteriores'), fn ($q) => $q->where('fecha_apertura', '<', today()))
            ->get();

        $resumen = [];
        foreach ($sesiones as $s) {
            $cerrada = $this->caja->cerrar($s, null, 'automatico', null, 'Cierre automático del sistema');
            $resumen[] = [
                'caja' => $s->caja->nombre,
                'usuario' => $s->usuarioApertura->nombre_completo ?? '—',
                'apertura' => $s->fecha_apertura->format('d/m/Y H:i'),
                'ingresos' => Formato::dinero($cerrada->total_ingresos),
                'egresos' => Formato::dinero($cerrada->total_egresos),
                'esperado' => Formato::dinero($cerrada->monto_esperado),
            ];
        }

        if ($resumen) {
            $this->enviarATodos($a, 'Cierre automático de cajas - ' . $a->tienda->nombre, 'cierre_caja', [
                'tienda' => $a->tienda->nombre, 'fecha' => now()->format('d/m/Y H:i'), 'cajas' => $resumen,
            ]);
        }

        return ['mensaje' => count($resumen) . ' caja(s) cerrada(s).', 'detalle' => $resumen];
    }

    private function runResumenDiarioVentas(Automatizacion $a): array
    {
        $ventas = Venta::where('tienda_id', $a->tienda_id)->where('estado', 'completada')->whereDate('fecha', today());
        $devoluciones = (float) DevolucionVenta::where('tienda_id', $a->tienda_id)->whereDate('fecha', today())->sum('total');

        $datos = [
            'tienda' => $a->tienda->nombre,
            'fecha' => today()->format('d/m/Y'),
            'cantidad' => (clone $ventas)->count(),
            'total' => Formato::dinero((clone $ventas)->sum('total')),
            'contado' => Formato::dinero((clone $ventas)->where('tipo_pago', 'contado')->sum('total')),
            'credito' => Formato::dinero((clone $ventas)->where('tipo_pago', 'credito')->sum('total')),
            'devoluciones' => Formato::dinero($devoluciones),
            'cajas_abiertas' => CajaSesion::where('estado', 'abierta')->whereHas('caja', fn ($q) => $q->where('tienda_id', $a->tienda_id))->count(),
        ];

        $this->enviarATodos($a, "Resumen de ventas {$datos['fecha']} - {$datos['tienda']}", 'resumen_ventas', $datos);

        return ['mensaje' => "{$datos['cantidad']} venta(s), Bs {$datos['total']}.", 'detalle' => $datos];
    }

    private function runAlertaStockMinimo(Automatizacion $a): array
    {
        $filas = Inventario::with(['producto', 'almacen'])
            ->whereHas('almacen', fn ($q) => $q->where('tienda_id', $a->tienda_id))
            ->where('stock_minimo', '>', 0)
            ->whereColumn('cantidad', '<=', 'stock_minimo')
            ->whereNull('alerta_stock_enviada_at')
            ->get();

        if ($filas->isEmpty()) {
            return ['estado' => 'omitido', 'mensaje' => 'Sin productos nuevos bajo el mínimo.'];
        }

        $items = $filas->map(fn ($i) => [
            'producto' => $i->producto->codigo . ' - ' . $i->producto->nombre,
            'almacen' => $i->almacen->nombre,
            'stock' => Formato::cantidad($i->cantidad),
            'minimo' => Formato::cantidad($i->stock_minimo),
        ])->all();

        $this->enviarATodos($a, 'Alerta de stock mínimo - ' . $a->tienda->nombre, 'alerta_stock', ['tienda' => $a->tienda->nombre, 'items' => $items]);
        Inventario::whereIn('id', $filas->pluck('id'))->update(['alerta_stock_enviada_at' => now()]);

        return ['mensaje' => count($items) . ' producto(s) bajo el mínimo.', 'detalle' => $items];
    }

    private function runAlertaVencimientoLotes(Automatizacion $a): array
    {
        $dias = (int) $a->parametro('dias_aviso', 30);

        $lotes = Lote::with(['producto', 'almacen'])
            ->whereHas('almacen', fn ($q) => $q->where('tienda_id', $a->tienda_id))
            ->where('cantidad_actual', '>', 0)
            ->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($dias)->toDateString())
            ->whereNull('alerta_vencimiento_enviada_at')
            ->orderBy('fecha_vencimiento')
            ->get();

        if ($lotes->isEmpty()) {
            return ['estado' => 'omitido', 'mensaje' => 'Sin lotes nuevos por vencer.'];
        }

        $items = $lotes->map(fn ($l) => [
            'producto' => $l->producto->codigo . ' - ' . $l->producto->nombre,
            'almacen' => $l->almacen->nombre,
            'lote' => $l->numero_lote,
            'vence' => $l->fecha_vencimiento->format('d/m/Y'),
            'dias' => $l->dias_para_vencer,
            'cantidad' => Formato::cantidad($l->cantidad_actual),
        ])->all();

        $this->enviarATodos($a, 'Alerta de vencimiento de lotes - ' . $a->tienda->nombre, 'alerta_vencimiento', ['tienda' => $a->tienda->nombre, 'items' => $items]);
        Lote::whereIn('id', $lotes->pluck('id'))->update(['alerta_vencimiento_enviada_at' => now()]);

        return ['mensaje' => count($items) . ' lote(s) por vencer o vencidos.', 'detalle' => $items];
    }

    private function runRecordatorioCxc(Automatizacion $a): array
    {
        $antes = (int) $a->parametro('dias_antes', 3);
        $cada = (int) $a->parametro('cada_dias', 7);
        $max = (int) $a->parametro('max_recordatorios', 5);

        $cuentas = CuentaPorCobrar::with(['cliente', 'tienda'])
            ->where('tienda_id', $a->tienda_id)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->where('saldo', '>', 0)
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($antes)->toDateString())
            ->where('recordatorios_enviados', '<', $max)
            ->where(fn ($q) => $q->whereNull('ultimo_recordatorio_at')->orWhere('ultimo_recordatorio_at', '<=', now()->subDays($cada)))
            ->orderBy('fecha_vencimiento')
            ->get();

        if ($cuentas->isEmpty()) {
            return ['estado' => 'omitido', 'mensaje' => 'Sin documentos para recordar.'];
        }

        $aClientes = 0;
        $resumen = [];
        foreach ($cuentas as $c) {
            $dias = (int) today()->diffInDays($c->fecha_vencimiento, false);
            $resumen[] = [
                'tercero' => $c->cliente->nombre, 'documento' => $c->numero_documento,
                'vence' => $c->fecha_vencimiento->format('d/m/Y'), 'dias' => $dias, 'saldo' => Formato::dinero($c->saldo),
            ];

            if ($c->cliente->email) {
                $this->encolar($c->cliente->email, "Recordatorio de pago - {$c->numero_documento}", 'recordatorio_cxc', [
                    'cliente' => $c->cliente->nombre, 'tienda' => $c->tienda->nombre, 'documento' => $c->numero_documento,
                    'vence' => $c->fecha_vencimiento->format('d/m/Y'), 'dias' => $dias, 'saldo' => Formato::dinero($c->saldo),
                ], $a->tienda_id, $a->id);
                $aClientes++;
            }

            $c->update(['recordatorios_enviados' => $c->recordatorios_enviados + 1, 'ultimo_recordatorio_at' => now()]);
        }

        $this->enviarATodos($a, 'Cuentas por cobrar por vencer o vencidas - ' . $a->tienda->nombre, 'alerta_cuentas', [
            'titulo' => 'Cuentas por cobrar', 'tienda' => $a->tienda->nombre, 'tercero' => 'Cliente', 'items' => $resumen,
        ]);

        return ['mensaje' => count($resumen) . " documento(s); {$aClientes} recordatorio(s) a clientes.", 'detalle' => $resumen];
    }

    private function runAvisoCxp(Automatizacion $a): array
    {
        $antes = (int) $a->parametro('dias_antes', 3);

        $cuentas = CuentaPorPagar::with('proveedor')
            ->where('tienda_id', $a->tienda_id)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->where('saldo', '>', 0)
            ->whereDate('fecha_vencimiento', '<=', today()->addDays($antes)->toDateString())
            ->where(fn ($q) => $q->whereNull('ultima_alerta_at')->orWhere('ultima_alerta_at', '<', today()))
            ->orderBy('fecha_vencimiento')
            ->get();

        if ($cuentas->isEmpty()) {
            return ['estado' => 'omitido', 'mensaje' => 'Sin pagos próximos.'];
        }

        $items = $cuentas->map(fn ($c) => [
            'tercero' => $c->proveedor->nombre, 'documento' => $c->numero_documento,
            'vence' => $c->fecha_vencimiento->format('d/m/Y'),
            'dias' => (int) today()->diffInDays($c->fecha_vencimiento, false), 'saldo' => Formato::dinero($c->saldo),
        ])->all();

        $this->enviarATodos($a, 'Pagos a proveedores próximos o vencidos - ' . $a->tienda->nombre, 'alerta_cuentas', [
            'titulo' => 'Cuentas por pagar', 'tienda' => $a->tienda->nombre, 'tercero' => 'Proveedor', 'items' => $items,
        ]);
        CuentaPorPagar::whereIn('id', $cuentas->pluck('id'))->update(['ultima_alerta_at' => now()]);

        return ['mensaje' => count($items) . ' documento(s) por pagar.', 'detalle' => $items];
    }

    private function runActualizarEstados(Automatizacion $a): array
    {
        $cxc = CuentaPorCobrar::where('tienda_id', $a->tienda_id)->whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0)->whereDate('fecha_vencimiento', '<', today()->toDateString())->update(['estado' => 'vencida']);

        $cxp = CuentaPorPagar::where('tienda_id', $a->tienda_id)->whereIn('estado', ['pendiente', 'parcial'])
            ->where('saldo', '>', 0)->whereDate('fecha_vencimiento', '<', today()->toDateString())->update(['estado' => 'vencida']);

        $lotes = Lote::whereHas('almacen', fn ($q) => $q->where('tienda_id', $a->tienda_id))
            ->where('cantidad_actual', '>', 0)->where('estado', 1)->whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<', today()->toDateString())->update(['estado' => 0]);

        $cot = Cotizacion::where('tienda_id', $a->tienda_id)->where('estado', 'pendiente')
            ->whereNotNull('fecha_validez')->whereDate('fecha_validez', '<', today()->toDateString())->update(['estado' => 'vencida']);

        return ['mensaje' => "CxC: {$cxc} · CxP: {$cxp} · lotes: {$lotes} · cotizaciones: {$cot}", 'detalle' => compact('cxc', 'cxp', 'lotes', 'cot')];
    }

    private function runRespaldoBd(Automatizacion $a): array
    {
        $cn = config('database.connections.' . config('database.default'));
        if (! in_array($cn['driver'] ?? '', ['mysql', 'mariadb'], true)) {
            throw new \RuntimeException('El respaldo automático solo soporta MySQL/MariaDB.');
        }

        $bin = $a->parametro('ruta_mysqldump') ?: 'mysqldump';
        $dir = storage_path('app/backups');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $archivo = $dir . '/' . $cn['database'] . '_' . now()->format('Ymd_His') . '.sql.gz';
        $gz = gzopen($archivo, 'wb9');

        $proceso = new Process([
            $bin, '--host=' . $cn['host'], '--port=' . ($cn['port'] ?? 3306), '--user=' . $cn['username'],
            '--single-transaction', '--routines', '--triggers', $cn['database'],
        ], null, ['MYSQL_PWD' => $cn['password'] ?? '']);
        $proceso->setTimeout(900);

        $err = '';
        $proceso->run(function ($tipo, $buffer) use ($gz, &$err) {
            if ($tipo === Process::OUT) {
                gzwrite($gz, $buffer);
            } else {
                $err .= $buffer;
            }
        });
        gzclose($gz);

        if (! $proceso->isSuccessful()) {
            @unlink($archivo);
            throw new \RuntimeException('mysqldump falló: ' . Str::limit(trim($err), 200, ''));
        }

        $conservar = max(1, (int) $a->parametro('conservar', 7));
        $archivos = collect(glob($dir . '/' . $cn['database'] . '_*.sql.gz') ?: [])->sort()->values();
        $borrar = $archivos->slice(0, max(0, $archivos->count() - $conservar));
        foreach ($borrar as $f) {
            @unlink($f);
        }

        return [
            'mensaje' => 'Respaldo generado: ' . basename($archivo) . ' (' . round(filesize($archivo) / 1024) . ' KB)',
            'detalle' => ['archivo' => basename($archivo), 'eliminados' => $borrar->count()],
        ];
    }

    /* ======================= Correo ======================= */

    private function encolar(string $destinatario, string $asunto, string $plantilla, array $datos, ?int $tiendaId, ?int $automatizacionId): ColaCorreo
    {
        return ColaCorreo::create([
            'tienda_id' => $tiendaId,
            'automatizacion_id' => $automatizacionId,
            'destinatario' => $destinatario,
            'asunto' => $asunto,
            'plantilla' => $plantilla,
            'datos' => $datos,
            'estado' => 'pendiente',
            'intentos' => 0,
        ]);
    }

    private function enviarATodos(Automatizacion $a, string $asunto, string $plantilla, array $datos): void
    {
        foreach ($this->destinatarios($a) as $correo) {
            $this->encolar($correo, $asunto, $plantilla, $datos, $a->tienda_id, $a->id);
        }
    }

    /** Correos del parámetro "correos"; si está vacío, administradores globales y de la tienda. */
    private function destinatarios(Automatizacion $a): array
    {
        $manuales = collect(preg_split('/[,;\s]+/', (string) $a->parametro('correos', '')) ?: [])
            ->map(fn ($c) => trim($c))
            ->filter(fn ($c) => filter_var($c, FILTER_VALIDATE_EMAIL))
            ->unique()->values()->all();

        if ($manuales) {
            return $manuales;
        }

        return User::query()
            ->whereNotNull('email')
            ->where(function ($q) use ($a) {
                $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['admin', 'administrador_general']));
                if ($a->tienda_id) {
                    $q->orWhere(fn ($q2) => $q2
                        ->whereHas('roles', fn ($r) => $r->where('name', 'administrador_tienda'))
                        ->whereHas('tiendas', fn ($t) => $t->where('tiendas.id', $a->tienda_id)));
                }
            })
            ->pluck('email')->unique()->values()->all();
    }
}
