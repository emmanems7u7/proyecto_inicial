<?php

namespace App\Services;

use App\Exceptions\OperacionException;
use App\Models\Almacen;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CuentaPorCobrar;
use App\Models\DevolucionVenta;
use App\Models\DevolucionVentaDetalle;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Support\Formato;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function __construct(
        private InventarioService $inventario,
        private CajaService $caja,
        private CuentaService $cuentas,
        private SecuenciaService $secuencias,
        private AutomatizacionService $automatizacion,
    ) {
    }

    /**
     * $d: almacen_id, cliente_id?, tipo_pago (contado|credito), metodo_pago_id?, anticipo?,
     *     descuento?, como_pedido?, cotizacion_id?, observaciones?, items[]{producto_id,cantidad,precio_unitario,descuento}
     */
    public function registrar(Tienda $tienda, array $d, User $user): Venta
    {
        $venta = DB::transaction(function () use ($tienda, $d, $user) {
            $almacen = Almacen::where('tienda_id', $tienda->id)->where('estado', 1)->find($d['almacen_id'] ?? 0);
            if (! $almacen) {
                throw new OperacionException('Selecciona un almacén válido de la tienda activa.');
            }

            $lineas = $this->normalizarLineas($tienda->empresa_id, $d['items'] ?? []);
            $subtotal = round(array_sum(array_column($lineas, 'subtotal')), 2);
            $descuento = round((float) ($d['descuento'] ?? 0), 2);
            if ($descuento > $subtotal) {
                throw new OperacionException('El descuento no puede superar el subtotal.');
            }
            $total = round($subtotal - $descuento, 2);
            if ($total <= 0) {
                throw new OperacionException('El total de la venta debe ser mayor a cero.');
            }

            $pedido = ! empty($d['como_pedido']);

            $venta = Venta::create([
                'tienda_id' => $tienda->id,
                'almacen_id' => $almacen->id,
                'cliente_id' => ! empty($d['cliente_id']) ? $d['cliente_id'] : null,
                'vendedor_id' => $user->id,
                'cotizacion_id' => $d['cotizacion_id'] ?? null,
                'numero' => $this->secuencias->siguiente($tienda->id, 'venta', 'V'),
                'fecha' => now(),
                'tipo_pago' => $d['tipo_pago'] ?? 'contado',
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'pagado' => 0,
                'saldo' => $total,
                'estado' => $pedido ? 'pedido' : 'completada',
                'observaciones' => $d['observaciones'] ?? null,
            ]);

            if ($pedido) {
                $this->guardarLineasPedido($venta, $lineas);
            } else {
                $this->completar($venta, $lineas, $d, $user);
            }

            if (! empty($d['cotizacion_id'])) {
                Cotizacion::where('id', $d['cotizacion_id'])->where('tienda_id', $tienda->id)->update(['estado' => 'convertida']);
            }

            AuditoriaService::registrar('ventas', $pedido ? 'crear_pedido' : 'crear', $venta, null,
                ['numero' => $venta->numero, 'total' => $total, 'tipo_pago' => $venta->tipo_pago], $tienda->id);

            return $venta->refresh();
        });

        if ($venta->estado === 'completada') {
            $this->automatizacion->encolarComprobanteVenta($venta);
        }

        return $venta;
    }

    /** Convierte un pedido en venta: descuenta stock y registra el cobro. */
    public function confirmarPedido(Venta $venta, array $d, User $user): Venta
    {
        $venta = DB::transaction(function () use ($venta, $d, $user) {
            $v = Venta::with('detalles')->lockForUpdate()->findOrFail($venta->id);
            if ($v->estado !== 'pedido') {
                throw new OperacionException('Solo los pedidos pueden confirmarse.');
            }

            $lineas = $v->detalles->map(fn ($x) => [
                'producto' => Producto::findOrFail($x->producto_id),
                'cantidad' => (float) $x->cantidad,
                'precio' => (float) $x->precio_unitario,
                'descuento' => (float) $x->descuento,
                'subtotal' => (float) $x->subtotal,
            ])->all();

            $v->update(['tipo_pago' => $d['tipo_pago'] ?? 'contado', 'estado' => 'completada', 'fecha' => now()]);
            $this->completar($v, $lineas, $d, $user);

            AuditoriaService::registrar('ventas', 'confirmar_pedido', $v, null, ['numero' => $v->numero], $v->tienda_id);

            return $v->refresh();
        });

        $this->automatizacion->encolarComprobanteVenta($venta);

        return $venta;
    }

    public function anular(Venta $venta, string $motivo, User $user): Venta
    {
        return DB::transaction(function () use ($venta, $motivo, $user) {
            $v = Venta::with('detalles.lote')->lockForUpdate()->findOrFail($venta->id);

            if ($v->estado === 'anulada') {
                throw new OperacionException('La venta ya está anulada.');
            }
            if ($v->devoluciones()->exists()) {
                throw new OperacionException('La venta tiene devoluciones registradas; no se puede anular.');
            }

            if ($v->estado === 'completada') {
                foreach ($v->detalles as $d) {
                    $this->reingresar($v->almacen_id, $d->producto_id, $d->lote, (float) $d->cantidad, $v, "Anulación venta {$v->numero}");
                }

                $reembolso = (float) $v->pagado;
                if ($reembolso > 0) {
                    $sesion = $this->caja->sesionAbiertaDeUsuario($user->id, $v->tienda_id);
                    $efectivo = $this->caja->metodoEfectivoId();
                    if (! $sesion || ! $efectivo) {
                        throw new OperacionException('Abre una caja para registrar la devolución del dinero cobrado (Bs ' . Formato::dinero($reembolso) . ').');
                    }
                    $this->caja->registrarMovimiento($sesion, 'egreso', "Anulación venta {$v->numero}", $reembolso, $efectivo, $v, $user->id);
                }

                CuentaPorCobrar::where('venta_id', $v->id)->update(['estado' => 'anulada', 'saldo' => 0]);
            }

            $v->update([
                'estado' => 'anulada',
                'anulada_at' => now(),
                'anulada_por' => $user->id,
                'motivo_anulacion' => $motivo,
                'saldo' => 0,
            ]);

            AuditoriaService::registrar('ventas', 'anular', $v, null, ['numero' => $v->numero, 'motivo' => $motivo], $v->tienda_id);

            return $v;
        });
    }

    /**
     * Cantidades vendidas, devueltas y disponibles para devolver, agrupadas por producto + lote.
     *
     * @return Collection<int, object>
     */
    public function pendientesDevolucion(Venta $venta): Collection
    {
        $clave = fn ($d) => $d->producto_id . '-' . ($d->lote_id ?? 0);

        $vendidos = $venta->detalles()->with(['producto', 'lote'])->get()->groupBy($clave);

        $devueltos = DevolucionVentaDetalle::whereHas('devolucion', fn ($q) => $q->where('venta_id', $venta->id)->where('estado', 'completada'))
            ->get()
            ->groupBy($clave)
            ->map(fn ($g) => (float) $g->sum('cantidad'));

        return $vendidos->map(function ($g, $k) use ($devueltos) {
            $primero = $g->first();
            $cantidad = (float) $g->sum('cantidad');
            $devuelto = (float) ($devueltos[$k] ?? 0);

            return (object) [
                'clave' => $k,
                'producto_id' => $primero->producto_id,
                'lote_id' => $primero->lote_id,
                'producto' => $primero->producto,
                'lote' => $primero->lote,
                'vendido' => $cantidad,
                'devuelto' => $devuelto,
                'disponible' => round($cantidad - $devuelto, 3),
                'precio' => $cantidad > 0 ? round($g->sum('subtotal') / $cantidad, 4) : 0,
            ];
        })->values();
    }

    /** $cantidades: [clave => cantidad a devolver] con la clave de pendientesDevolucion(). */
    public function devolver(Venta $venta, array $cantidades, ?string $motivo, User $user): DevolucionVenta
    {
        return DB::transaction(function () use ($venta, $cantidades, $motivo, $user) {
            $v = Venta::lockForUpdate()->findOrFail($venta->id);
            if ($v->estado !== 'completada') {
                throw new OperacionException('Solo se pueden devolver productos de ventas completadas.');
            }

            $pendientes = $this->pendientesDevolucion($v)->keyBy('clave');
            $items = [];
            foreach ($cantidades as $clave => $cantidad) {
                $cantidad = round((float) $cantidad, 3);
                if ($cantidad <= 0) {
                    continue;
                }
                $p = $pendientes->get($clave);
                if (! $p) {
                    throw new OperacionException('Hay un producto que no pertenece a la venta.');
                }
                if ($cantidad > $p->disponible + 0.0005) {
                    throw new OperacionException("La cantidad a devolver de «{$p->producto->nombre}» excede lo disponible ({$p->disponible}).");
                }
                $items[] = [$p, $cantidad];
            }
            if (! $items) {
                throw new OperacionException('Indica al menos una cantidad a devolver.');
            }

            $dev = DevolucionVenta::create([
                'tienda_id' => $v->tienda_id,
                'venta_id' => $v->id,
                'user_id' => $user->id,
                'numero' => $this->secuencias->siguiente($v->tienda_id, 'devolucion_venta', 'DV'),
                'fecha' => today()->toDateString(),
                'total' => 0,
                'motivo' => $motivo,
                'estado' => 'completada',
            ]);

            $total = 0;
            foreach ($items as [$p, $cantidad]) {
                $sub = round($cantidad * $p->precio, 2);
                $total += $sub;

                DevolucionVentaDetalle::create([
                    'devolucion_venta_id' => $dev->id,
                    'producto_id' => $p->producto_id,
                    'lote_id' => $p->lote_id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $p->precio,
                    'subtotal' => $sub,
                ]);

                $this->reingresar($v->almacen_id, $p->producto_id, $p->lote, $cantidad, $dev, "Devolución {$dev->numero}");
            }
            $total = round($total, 2);
            $dev->update(['total' => $total]);

            // Efecto financiero: primero baja la deuda del cliente; el resto se reembolsa en efectivo.
            $reembolso = 0.0;
            $cxc = CuentaPorCobrar::where('venta_id', $v->id)->where('estado', '!=', 'anulada')->lockForUpdate()->first();

            if ($cxc) {
                $nuevoTotal = round($cxc->monto_total - $total, 2);
                $pagado = (float) $cxc->monto_pagado;
                if ($nuevoTotal < $pagado) {
                    $reembolso = round($pagado - $nuevoTotal, 2);
                    $pagado = max(0, $nuevoTotal);
                }
                $saldo = max(0, round($nuevoTotal - $pagado, 2));
                $cxc->update([
                    'monto_total' => max(0, $nuevoTotal),
                    'monto_pagado' => $pagado,
                    'saldo' => $saldo,
                    'estado' => CuentaService::estadoPara(max(0, $nuevoTotal), $pagado, $cxc->fecha_vencimiento?->toDateString()),
                ]);
                $v->update(['pagado' => $pagado, 'saldo' => $saldo]);
            } else {
                $reembolso = $total;
                $v->update(['pagado' => max(0, round($v->pagado - $total, 2))]);
            }

            if ($reembolso > 0) {
                $sesion = $this->caja->sesionAbiertaDeUsuario($user->id, $v->tienda_id);
                $efectivo = $this->caja->metodoEfectivoId();
                if (! $sesion || ! $efectivo) {
                    throw new OperacionException('Abre una caja para registrar el reembolso de Bs ' . Formato::dinero($reembolso) . '.');
                }
                $this->caja->registrarMovimiento($sesion, 'egreso', "Devolución {$dev->numero} (venta {$v->numero})", $reembolso, $efectivo, $dev, $user->id);
            }

            AuditoriaService::registrar('ventas', 'devolucion', $dev, null, ['venta' => $v->numero, 'total' => $total, 'reembolso' => $reembolso], $v->tienda_id);

            return $dev;
        });
    }

    /* ---------------- internos ---------------- */

    /** @return array<int, array{producto: Producto, cantidad: float, precio: float, descuento: float, subtotal: float}> */
    private function normalizarLineas(int $empresaId, array $items): array
    {
        $ids = collect($items)->pluck('producto_id')->filter()->unique();
        $productos = Producto::where('empresa_id', $empresaId)->where('estado', 1)->whereIn('id', $ids)->get()->keyBy('id');

        $lineas = [];
        foreach ($items as $i) {
            $p = $productos->get($i['producto_id'] ?? 0);
            if (! $p) {
                throw new OperacionException('Hay un producto inválido o inactivo en el detalle.');
            }
            $cantidad = round((float) ($i['cantidad'] ?? 0), 3);
            $precio = round((float) ($i['precio_unitario'] ?? 0), 2);
            $descuento = round((float) ($i['descuento'] ?? 0), 2);
            if ($cantidad <= 0) {
                throw new OperacionException("La cantidad de «{$p->nombre}» debe ser mayor a cero.");
            }
            $bruto = round($cantidad * $precio, 2);
            if ($descuento > $bruto) {
                throw new OperacionException("El descuento de «{$p->nombre}» supera su importe.");
            }
            $lineas[] = ['producto' => $p, 'cantidad' => $cantidad, 'precio' => $precio, 'descuento' => $descuento, 'subtotal' => round($bruto - $descuento, 2)];
        }

        if (! $lineas) {
            throw new OperacionException('Agrega al menos un producto.');
        }

        return $lineas;
    }

    private function guardarLineasPedido(Venta $venta, array $lineas): void
    {
        foreach ($lineas as $l) {
            VentaDetalle::create([
                'venta_id' => $venta->id,
                'producto_id' => $l['producto']->id,
                'lote_id' => null,
                'cantidad' => $l['cantidad'],
                'precio_unitario' => $l['precio'],
                'costo_unitario' => $l['producto']->precio_compra,
                'descuento' => $l['descuento'],
                'subtotal' => $l['subtotal'],
            ]);
        }
    }

    /** Descuenta stock (FEFO) y crea un detalle por cada lote consumido. */
    private function descontarStock(Venta $venta, array $lineas): void
    {
        $venta->detalles()->delete();

        foreach ($lineas as $l) {
            $movs = $this->inventario->salida($venta->almacen_id, $l['producto']->id, $l['cantidad'], 'venta', [
                'referencia' => $venta,
                'observacion' => "Venta {$venta->numero}",
            ]);

            $restoDescuento = $l['descuento'];
            $total = $movs->count();
            $n = 0;
            foreach ($movs as $m) {
                $n++;
                $cantidad = abs($m->cantidad);
                $descuento = $n === $total ? $restoDescuento : round($l['descuento'] * $cantidad / $l['cantidad'], 2);
                $restoDescuento = round($restoDescuento - $descuento, 2);

                VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $l['producto']->id,
                    'lote_id' => $m->lote_id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $l['precio'],
                    'costo_unitario' => $m->costo_unitario,
                    'descuento' => $descuento,
                    'subtotal' => round($cantidad * $l['precio'] - $descuento, 2),
                ]);
            }
        }
    }

    private function completar(Venta $venta, array $lineas, array $d, User $user): void
    {
        $credito = $venta->tipo_pago === 'credito';
        $metodoId = ! empty($d['metodo_pago_id']) ? (int) $d['metodo_pago_id'] : null;
        $anticipo = $credito ? round((float) ($d['anticipo'] ?? 0), 2) : (float) $venta->total;
        $cliente = null;

        if ($credito) {
            $cliente = $venta->cliente_id
                ? Cliente::where('empresa_id', $venta->tienda->empresa_id)->find($venta->cliente_id)
                : null;
            if (! $cliente) {
                throw new OperacionException('La venta a crédito requiere seleccionar un cliente.');
            }
            if ($cliente->limite_credito <= 0) {
                throw new OperacionException('El cliente no tiene crédito habilitado (límite en 0).');
            }
            if ($anticipo < 0 || $anticipo >= $venta->total) {
                throw new OperacionException('El anticipo debe ser mayor o igual a 0 y menor al total.');
            }

            $pendiente = (float) CuentaPorCobrar::where('cliente_id', $cliente->id)->whereNotIn('estado', ['pagada', 'anulada'])->sum('saldo');
            $nuevo = round($venta->total - $anticipo, 2);
            if ($pendiente + $nuevo > $cliente->limite_credito + 0.005) {
                throw new OperacionException(sprintf(
                    'Supera el límite de crédito del cliente (límite Bs %s, deuda actual Bs %s, esta venta Bs %s).',
                    Formato::dinero($cliente->limite_credito), Formato::dinero($pendiente), Formato::dinero($nuevo)
                ));
            }
        }

        $sesion = null;
        if ($anticipo > 0) {
            if (! $metodoId) {
                throw new OperacionException('Selecciona el método de pago.');
            }
            $sesion = $this->caja->sesionAbiertaDeUsuario($user->id, $venta->tienda_id);
            if (! $sesion) {
                throw new OperacionException('Debes abrir una caja para registrar el cobro de esta venta.');
            }
        }

        $this->descontarStock($venta, $lineas);

        if (! $credito) {
            $this->caja->registrarMovimiento($sesion, 'ingreso', "Venta {$venta->numero}", (float) $venta->total, $metodoId, $venta, $user->id);
            $venta->update(['pagado' => $venta->total, 'saldo' => 0, 'caja_sesion_id' => $sesion->id]);

            return;
        }

        $cxc = $this->cuentas->crearCxc($venta, $cliente, now()->addDays($cliente->dias_credito ?: 30)->toDateString());
        if ($anticipo > 0) {
            $this->cuentas->pagarCxc($cxc, $anticipo, $metodoId, $user->id, null, 'Anticipo de venta', $sesion);
        }
        if ($sesion) {
            $venta->update(['caja_sesion_id' => $sesion->id]);
        }
    }

    private function reingresar(int $almacenId, int $productoId, ?Lote $lote, float $cantidad, $referencia, string $observacion): void
    {
        $opts = ['referencia' => $referencia, 'observacion' => $observacion];
        if ($lote) {
            $opts['numero_lote'] = $lote->numero_lote;
            $opts['fecha_vencimiento'] = $lote->fecha_vencimiento?->toDateString();
            $opts['costo_unitario'] = $lote->costo_unitario;
        }

        $this->inventario->entrada($almacenId, $productoId, $cantidad, 'devolucion_venta', $opts);
    }
}
