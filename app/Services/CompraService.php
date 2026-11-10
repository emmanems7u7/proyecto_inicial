<?php

namespace App\Services;

use App\Exceptions\OperacionException;
use App\Models\Almacen;
use App\Models\CajaMovimiento;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\CuentaPorPagar;
use App\Models\DevolucionCompra;
use App\Models\DevolucionCompraDetalle;
use App\Models\Lote;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Tienda;
use App\Models\User;
use App\Support\Formato;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function __construct(
        private InventarioService $inventario,
        private CajaService $caja,
        private CuentaService $cuentas,
        private SecuenciaService $secuencias,
    ) {
    }

    /**
     * $d: almacen_id, proveedor_id, numero_documento_proveedor?, tipo_pago, pagar_caja?, metodo_pago_id?,
     *     descuento?, orden_compra_id?, observaciones?, items[]{producto_id,cantidad,costo_unitario,descuento,numero_lote?,fecha_vencimiento?}
     */
    public function registrar(Tienda $tienda, array $d, User $user): Compra
    {
        return DB::transaction(function () use ($tienda, $d, $user) {
            $almacen = Almacen::where('tienda_id', $tienda->id)->where('estado', 1)->find($d['almacen_id'] ?? 0);
            if (! $almacen) {
                throw new OperacionException('Selecciona un almacén válido de la tienda activa.');
            }
            $proveedor = Proveedor::where('empresa_id', $tienda->empresa_id)->find($d['proveedor_id'] ?? 0);
            if (! $proveedor) {
                throw new OperacionException('Selecciona un proveedor.');
            }

            $lineas = $this->normalizarLineas($tienda->empresa_id, $d['items'] ?? []);
            $subtotal = round(array_sum(array_column($lineas, 'subtotal')), 2);
            $descuento = round((float) ($d['descuento'] ?? 0), 2);
            if ($descuento > $subtotal) {
                throw new OperacionException('El descuento no puede superar el subtotal.');
            }
            $total = round($subtotal - $descuento, 2);
            if ($total <= 0) {
                throw new OperacionException('El total de la compra debe ser mayor a cero.');
            }

            $credito = ($d['tipo_pago'] ?? 'contado') === 'credito';
            $metodoId = ! empty($d['metodo_pago_id']) ? (int) $d['metodo_pago_id'] : null;
            $sesion = null;
            if (! $credito && ! empty($d['pagar_caja'])) {
                if (! $metodoId) {
                    throw new OperacionException('Selecciona el método de pago.');
                }
                $sesion = $this->caja->sesionAbiertaDeUsuario($user->id, $tienda->id);
                if (! $sesion) {
                    throw new OperacionException('Debes abrir una caja para registrar el egreso de esta compra.');
                }
            }

            $orden = null;
            if (! empty($d['orden_compra_id'])) {
                $orden = OrdenCompra::where('tienda_id', $tienda->id)->lockForUpdate()->find($d['orden_compra_id']);
                if (! $orden || in_array($orden->estado, ['recibida', 'anulada'])) {
                    throw new OperacionException('La orden de compra no está disponible para recepción.');
                }
            }

            $compra = Compra::create([
                'tienda_id' => $tienda->id,
                'almacen_id' => $almacen->id,
                'proveedor_id' => $proveedor->id,
                'orden_compra_id' => $orden?->id,
                'user_id' => $user->id,
                'numero' => $this->secuencias->siguiente($tienda->id, 'compra', 'C'),
                'numero_documento_proveedor' => $d['numero_documento_proveedor'] ?? null,
                'fecha' => now(),
                'tipo_pago' => $credito ? 'credito' : 'contado',
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'pagado' => 0,
                'saldo' => $total,
                'estado' => 'recibida',
                'observaciones' => $d['observaciones'] ?? null,
            ]);

            foreach ($lineas as $l) {
                $costoNeto = round($l['subtotal'] / $l['cantidad'], 4);
                $movs = $this->inventario->entrada($almacen->id, $l['producto']->id, $l['cantidad'], 'compra', [
                    'referencia' => $compra,
                    'numero_lote' => $l['numero_lote'],
                    'fecha_vencimiento' => $l['fecha_vencimiento'],
                    'costo_unitario' => $costoNeto,
                    'observacion' => "Compra {$compra->numero}",
                ]);
                $mov = $movs->first();
                $lote = $mov->lote_id ? Lote::find($mov->lote_id) : null;

                CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $l['producto']->id,
                    'lote_id' => $lote?->id,
                    'numero_lote' => $lote?->numero_lote,
                    'fecha_vencimiento' => $lote?->fecha_vencimiento?->toDateString(),
                    'cantidad' => $l['cantidad'],
                    'costo_unitario' => $l['costo'],
                    'descuento' => $l['descuento'],
                    'subtotal' => $l['subtotal'],
                ]);
            }

            if ($credito) {
                $this->cuentas->crearCxp($compra, $proveedor, now()->addDays($proveedor->dias_credito ?: 30)->toDateString());
            } else {
                $compra->update(['pagado' => $total, 'saldo' => 0]);
                if ($sesion) {
                    $this->caja->registrarMovimiento($sesion, 'egreso', "Compra {$compra->numero}", $total, $metodoId, $compra, $user->id);
                }
            }

            if ($orden) {
                $this->aplicarARecibido($orden, $this->sumarPorProducto($lineas), 1);
            }

            AuditoriaService::registrar('compras', 'crear', $compra, null,
                ['numero' => $compra->numero, 'total' => $total, 'tipo_pago' => $compra->tipo_pago], $tienda->id);

            return $compra->refresh();
        });
    }

    public function anular(Compra $compra, User $user): Compra
    {
        return DB::transaction(function () use ($compra, $user) {
            $c = Compra::with('detalles')->lockForUpdate()->findOrFail($compra->id);

            if ($c->estado === 'anulada') {
                throw new OperacionException('La compra ya está anulada.');
            }
            if ($c->devoluciones()->exists()) {
                throw new OperacionException('La compra tiene devoluciones registradas; no se puede anular.');
            }
            $cxp = CuentaPorPagar::where('compra_id', $c->id)->lockForUpdate()->first();
            if ($cxp && $cxp->monto_pagado > 0) {
                throw new OperacionException('La compra tiene pagos registrados en cuentas por pagar.');
            }

            foreach ($c->detalles as $d) {
                $this->inventario->salida($c->almacen_id, $d->producto_id, (float) $d->cantidad, 'devolucion_compra', [
                    'lote_id' => $d->lote_id,
                    'referencia' => $c,
                    'observacion' => "Anulación compra {$c->numero}",
                    'incluir_vencidos' => true,
                ]);
            }

            if ($cxp) {
                $cxp->update(['estado' => 'anulada', 'saldo' => 0]);
            }

            $egreso = CajaMovimiento::where('referencia_type', $c->getMorphClass())
                ->where('referencia_id', $c->id)->where('tipo', 'egreso')->first();
            if ($egreso) {
                $sesion = $this->caja->sesionAbiertaDeUsuario($user->id, $c->tienda_id);
                if (! $sesion) {
                    throw new OperacionException('Abre una caja para registrar el reintegro del dinero pagado en esta compra.');
                }
                $this->caja->registrarMovimiento($sesion, 'ingreso', "Anulación compra {$c->numero}", (float) $egreso->monto, $egreso->metodo_pago_id, $c, $user->id);
            }

            if ($c->orden_compra_id && ($orden = OrdenCompra::lockForUpdate()->find($c->orden_compra_id))) {
                $this->aplicarARecibido($orden, $c->detalles->groupBy('producto_id')->map(fn ($g) => (float) $g->sum('cantidad'))->all(), -1);
            }

            $c->update(['estado' => 'anulada', 'saldo' => 0]);

            AuditoriaService::registrar('compras', 'anular', $c, null, ['numero' => $c->numero], $c->tienda_id);

            return $c;
        });
    }

    /** @return Collection<int, object> */
    public function pendientesDevolucion(Compra $compra): Collection
    {
        $clave = fn ($d) => $d->producto_id . '-' . ($d->lote_id ?? 0);

        $comprados = $compra->detalles()->with(['producto', 'lote'])->get()->groupBy($clave);
        $devueltos = DevolucionCompraDetalle::whereHas('devolucion', fn ($q) => $q->where('compra_id', $compra->id)->where('estado', 'completada'))
            ->get()->groupBy($clave)->map(fn ($g) => (float) $g->sum('cantidad'));

        return $comprados->map(function ($g, $k) use ($devueltos) {
            $primero = $g->first();
            $cantidad = (float) $g->sum('cantidad');
            $devuelto = (float) ($devueltos[$k] ?? 0);

            return (object) [
                'clave' => $k,
                'producto_id' => $primero->producto_id,
                'lote_id' => $primero->lote_id,
                'producto' => $primero->producto,
                'lote' => $primero->lote,
                'comprado' => $cantidad,
                'devuelto' => $devuelto,
                'disponible' => round($cantidad - $devuelto, 3),
                'costo' => $cantidad > 0 ? round($g->sum('subtotal') / $cantidad, 4) : 0,
            ];
        })->values();
    }

    public function devolver(Compra $compra, array $cantidades, ?string $motivo, User $user): DevolucionCompra
    {
        return DB::transaction(function () use ($compra, $cantidades, $motivo, $user) {
            $c = Compra::lockForUpdate()->findOrFail($compra->id);
            if ($c->estado !== 'recibida') {
                throw new OperacionException('Solo se pueden devolver productos de compras recibidas.');
            }

            $pendientes = $this->pendientesDevolucion($c)->keyBy('clave');
            $items = [];
            foreach ($cantidades as $clave => $cantidad) {
                $cantidad = round((float) $cantidad, 3);
                if ($cantidad <= 0) {
                    continue;
                }
                $p = $pendientes->get($clave);
                if (! $p) {
                    throw new OperacionException('Hay un producto que no pertenece a la compra.');
                }
                if ($cantidad > $p->disponible + 0.0005) {
                    throw new OperacionException("La cantidad a devolver de «{$p->producto->nombre}» excede lo disponible ({$p->disponible}).");
                }
                $items[] = [$p, $cantidad];
            }
            if (! $items) {
                throw new OperacionException('Indica al menos una cantidad a devolver.');
            }

            $dev = DevolucionCompra::create([
                'tienda_id' => $c->tienda_id,
                'compra_id' => $c->id,
                'user_id' => $user->id,
                'numero' => $this->secuencias->siguiente($c->tienda_id, 'devolucion_compra', 'DC'),
                'fecha' => today()->toDateString(),
                'total' => 0,
                'motivo' => $motivo,
                'estado' => 'completada',
            ]);

            $total = 0;
            foreach ($items as [$p, $cantidad]) {
                $sub = round($cantidad * $p->costo, 2);
                $total += $sub;

                $this->inventario->salida($c->almacen_id, $p->producto_id, $cantidad, 'devolucion_compra', [
                    'lote_id' => $p->lote_id,
                    'referencia' => $dev,
                    'observacion' => "Devolución {$dev->numero}",
                    'incluir_vencidos' => true,
                ]);

                DevolucionCompraDetalle::create([
                    'devolucion_compra_id' => $dev->id,
                    'producto_id' => $p->producto_id,
                    'lote_id' => $p->lote_id,
                    'cantidad' => $cantidad,
                    'costo_unitario' => $p->costo,
                    'subtotal' => $sub,
                ]);
            }
            $total = round($total, 2);
            $dev->update(['total' => $total]);

            // Baja la deuda con el proveedor; si ya se pagó de más, el proveedor reintegra (ingreso en caja).
            $reintegro = 0.0;
            $cxp = CuentaPorPagar::where('compra_id', $c->id)->where('estado', '!=', 'anulada')->lockForUpdate()->first();
            if ($cxp) {
                $nuevoTotal = round($cxp->monto_total - $total, 2);
                $pagado = (float) $cxp->monto_pagado;
                if ($nuevoTotal < $pagado) {
                    $reintegro = round($pagado - $nuevoTotal, 2);
                    $pagado = max(0, $nuevoTotal);
                }
                $saldo = max(0, round($nuevoTotal - $pagado, 2));
                $cxp->update([
                    'monto_total' => max(0, $nuevoTotal),
                    'monto_pagado' => $pagado,
                    'saldo' => $saldo,
                    'estado' => CuentaService::estadoPara(max(0, $nuevoTotal), $pagado, $cxp->fecha_vencimiento?->toDateString()),
                ]);
                $c->update(['pagado' => $pagado, 'saldo' => $saldo]);
            } else {
                $reintegro = $total;
                $c->update(['pagado' => max(0, round($c->pagado - $total, 2))]);
            }

            if ($reintegro > 0 && CajaMovimientoExiste::paraCompra($c)) {
                $sesion = $this->caja->sesionAbiertaDeUsuario($user->id, $c->tienda_id);
                $efectivo = $this->caja->metodoEfectivoId();
                if (! $sesion || ! $efectivo) {
                    throw new OperacionException('Abre una caja para registrar el reintegro de Bs ' . Formato::dinero($reintegro) . '.');
                }
                $this->caja->registrarMovimiento($sesion, 'ingreso', "Devolución {$dev->numero} (compra {$c->numero})", $reintegro, $efectivo, $dev, $user->id);
            }

            AuditoriaService::registrar('compras', 'devolucion', $dev, null, ['compra' => $c->numero, 'total' => $total], $c->tienda_id);

            return $dev;
        });
    }

    /* ---------------- internos ---------------- */
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
            $costo = round((float) ($i['costo_unitario'] ?? 0), 2);
            $descuento = round((float) ($i['descuento'] ?? 0), 2);
            if ($cantidad <= 0) {
                throw new OperacionException("La cantidad de «{$p->nombre}» debe ser mayor a cero.");
            }
            $bruto = round($cantidad * $costo, 2);
            if ($descuento > $bruto) {
                throw new OperacionException("El descuento de «{$p->nombre}» supera su importe.");
            }
            $lineas[] = [
                'producto' => $p,
                'cantidad' => $cantidad,
                'costo' => $costo,
                'descuento' => $descuento,
                'subtotal' => round($bruto - $descuento, 2),
                'numero_lote' => ($i['numero_lote'] ?? null) ?: null,
                'fecha_vencimiento' => ($i['fecha_vencimiento'] ?? null) ?: null,
            ];
        }

        if (! $lineas) {
            throw new OperacionException('Agrega al menos un producto.');
        }

        return $lineas;
    }

    private function sumarPorProducto(array $lineas): array
    {
        $res = [];
        foreach ($lineas as $l) {
            $res[$l['producto']->id] = round(($res[$l['producto']->id] ?? 0) + $l['cantidad'], 3);
        }

        return $res;
    }

    /** Ajusta cantidad_recibida de la orden ($signo 1 = recepción, -1 = reversa) y recalcula su estado. */
    private function aplicarARecibido(OrdenCompra $orden, array $porProducto, int $signo): void
    {
        foreach ($orden->detalles()->lockForUpdate()->get() as $det) {
            $pid = $det->producto_id;
            if (empty($porProducto[$pid])) {
                continue;
            }
            $toma = $signo > 0
                ? min($porProducto[$pid], max(0, $det->cantidad - $det->cantidad_recibida))
                : min($porProducto[$pid], $det->cantidad_recibida);

            $det->cantidad_recibida = round($det->cantidad_recibida + $signo * $toma, 3);
            $det->save();
            $porProducto[$pid] = round($porProducto[$pid] - $toma, 3);
        }

        $detalles = $orden->detalles()->get();
        $completo = $detalles->every(fn ($x) => $x->cantidad_recibida + 0.0005 >= $x->cantidad);
        $algo = $detalles->contains(fn ($x) => $x->cantidad_recibida > 0);

        $orden->update(['estado' => $completo ? 'recibida' : ($algo ? 'parcial' : 'pendiente')]);
    }
}

/** Ayuda interna: ¿la compra se pagó desde caja? (para decidir si el reintegro vuelve a caja). */
class CajaMovimientoExiste
{
    public static function paraCompra(Compra $compra): bool
    {
        return CajaMovimiento::where('referencia_type', $compra->getMorphClass())
            ->where('referencia_id', $compra->id)->where('tipo', 'egreso')->exists();
    }
}
