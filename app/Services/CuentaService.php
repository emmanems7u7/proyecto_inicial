<?php

namespace App\Services;

use App\Exceptions\OperacionException;
use App\Models\CajaSesion;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\CxcPago;
use App\Models\CxpPago;
use App\Models\Proveedor;
use App\Models\Venta;
use App\Support\Formato;
use Illuminate\Support\Facades\DB;

/** Cuentas por cobrar (ventas a crédito) y por pagar (compras a crédito). */
class CuentaService
{
    public function __construct(private CajaService $caja)
    {
    }

    public static function estadoPara(float $total, float $pagado, ?string $vencimiento): string
    {
        if (round($total - $pagado, 2) <= 0.005) {
            return 'pagada';
        }
        if ($vencimiento && $vencimiento < today()->toDateString()) {
            return 'vencida';
        }

        return $pagado > 0 ? 'parcial' : 'pendiente';
    }

    /* ---------------- Por cobrar ---------------- */
    public function crearCxc(Venta $venta, Cliente $cliente, string $vencimiento): CuentaPorCobrar
    {
        return CuentaPorCobrar::create([
            'tienda_id' => $venta->tienda_id,
            'cliente_id' => $cliente->id,
            'venta_id' => $venta->id,
            'numero_documento' => $venta->numero,
            'fecha_emision' => today()->toDateString(),
            'fecha_vencimiento' => $vencimiento,
            'monto_total' => $venta->total,
            'monto_pagado' => 0,
            'saldo' => $venta->total,
            'estado' => 'pendiente',
        ]);
    }

    public function pagarCxc(CuentaPorCobrar $cxc, float $monto, int $metodoId, ?int $userId, ?string $referencia = null, ?string $observacion = null, ?CajaSesion $sesion = null): CxcPago
    {
        return DB::transaction(function () use ($cxc, $monto, $metodoId, $userId, $referencia, $observacion, $sesion) {
            $c = CuentaPorCobrar::lockForUpdate()->findOrFail($cxc->id);
            $monto = round($monto, 2);

            if (in_array($c->estado, ['pagada', 'anulada'])) {
                throw new OperacionException('El documento no admite más pagos.');
            }
            if ($monto <= 0) {
                throw new OperacionException('El monto debe ser mayor a cero.');
            }
            if ($monto > $c->saldo + 0.005) {
                throw new OperacionException('El pago excede el saldo pendiente (Bs ' . Formato::dinero($c->saldo) . ').');
            }

            $pago = CxcPago::create([
                'cuenta_por_cobrar_id' => $c->id,
                'caja_sesion_id' => $sesion?->id,
                'metodo_pago_id' => $metodoId,
                'user_id' => $userId,
                'monto' => $monto,
                'fecha' => now(),
                'referencia' => $referencia,
                'observacion' => $observacion,
            ]);

            $pagado = round($c->monto_pagado + $monto, 2);
            $saldo = round($c->monto_total - $pagado, 2);
            $c->update([
                'monto_pagado' => $pagado,
                'saldo' => max(0, $saldo),
                'estado' => self::estadoPara($c->monto_total, $pagado, $c->fecha_vencimiento?->toDateString()),
            ]);

            if ($c->venta_id) {
                Venta::whereKey($c->venta_id)->update(['pagado' => $pagado, 'saldo' => max(0, $saldo)]);
            }
            if ($sesion) {
                $this->caja->registrarMovimiento($sesion, 'ingreso', "Cobro {$c->numero_documento}", $monto, $metodoId, $pago, $userId);
            }

            AuditoriaService::registrar('cxc', 'cobro', $pago, null, ['documento' => $c->numero_documento, 'monto' => $monto], $c->tienda_id);

            return $pago;
        });
    }

    /* ---------------- Por pagar ---------------- */
    public function crearCxp(Compra $compra, Proveedor $proveedor, string $vencimiento): CuentaPorPagar
    {
        return CuentaPorPagar::create([
            'tienda_id' => $compra->tienda_id,
            'proveedor_id' => $proveedor->id,
            'compra_id' => $compra->id,
            'numero_documento' => $compra->numero_documento_proveedor ?: $compra->numero,
            'fecha_emision' => today()->toDateString(),
            'fecha_vencimiento' => $vencimiento,
            'monto_total' => $compra->total,
            'monto_pagado' => 0,
            'saldo' => $compra->total,
            'estado' => 'pendiente',
        ]);
    }

    public function pagarCxp(CuentaPorPagar $cxp, float $monto, int $metodoId, ?int $userId, ?string $referencia = null, ?string $observacion = null, ?CajaSesion $sesion = null): CxpPago
    {
        return DB::transaction(function () use ($cxp, $monto, $metodoId, $userId, $referencia, $observacion, $sesion) {
            $c = CuentaPorPagar::lockForUpdate()->findOrFail($cxp->id);
            $monto = round($monto, 2);

            if (in_array($c->estado, ['pagada', 'anulada'])) {
                throw new OperacionException('El documento no admite más pagos.');
            }
            if ($monto <= 0) {
                throw new OperacionException('El monto debe ser mayor a cero.');
            }
            if ($monto > $c->saldo + 0.005) {
                throw new OperacionException('El pago excede el saldo pendiente (Bs ' . Formato::dinero($c->saldo) . ').');
            }

            $pago = CxpPago::create([
                'cuenta_por_pagar_id' => $c->id,
                'caja_sesion_id' => $sesion?->id,
                'metodo_pago_id' => $metodoId,
                'user_id' => $userId,
                'monto' => $monto,
                'fecha' => now(),
                'referencia' => $referencia,
                'observacion' => $observacion,
            ]);

            $pagado = round($c->monto_pagado + $monto, 2);
            $saldo = round($c->monto_total - $pagado, 2);
            $c->update([
                'monto_pagado' => $pagado,
                'saldo' => max(0, $saldo),
                'estado' => self::estadoPara($c->monto_total, $pagado, $c->fecha_vencimiento?->toDateString()),
            ]);

            if ($c->compra_id) {
                Compra::whereKey($c->compra_id)->update(['pagado' => $pagado, 'saldo' => max(0, $saldo)]);
            }
            if ($sesion) {
                $this->caja->registrarMovimiento($sesion, 'egreso', "Pago {$c->numero_documento}", $monto, $metodoId, $pago, $userId);
            }

            AuditoriaService::registrar('cxp', 'pago', $pago, null, ['documento' => $c->numero_documento, 'monto' => $monto], $c->tienda_id);

            return $pago;
        });
    }
}
