<?php

namespace App\Services;

use App\Exceptions\OperacionException;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\CajaSesion;
use App\Models\Catalogo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CajaService
{
    /** Código del catálogo de métodos de pago que representa el efectivo físico. */
    public const CODIGO_EFECTIVO = 'MP-001';

    public function metodoEfectivoId(): ?int
    {
        return Catalogo::where('catalogo_codigo', self::CODIGO_EFECTIVO)->value('id');
    }

    public function sesionAbiertaDeUsuario(int $userId, int $tiendaId): ?CajaSesion
    {
        return CajaSesion::where('estado', 'abierta')
            ->where('user_apertura_id', $userId)
            ->whereHas('caja', fn ($q) => $q->where('tienda_id', $tiendaId))
            ->latest('id')
            ->first();
    }

    public function abrir(Caja $caja, float $montoApertura, int $userId, ?string $observaciones = null): CajaSesion
    {
        return DB::transaction(function () use ($caja, $montoApertura, $userId, $observaciones) {
            $caja = Caja::lockForUpdate()->findOrFail($caja->id);

            if ((int) $caja->estado !== 1) {
                throw new OperacionException('La caja está inactiva.');
            }
            if (CajaSesion::where('caja_id', $caja->id)->where('estado', 'abierta')->exists()) {
                throw new OperacionException('Esta caja ya está abierta.');
            }
            if ($this->sesionAbiertaDeUsuario($userId, $caja->tienda_id)) {
                throw new OperacionException('Ya tienes una caja abierta en esta tienda. Ciérrala antes de abrir otra.');
            }

            $sesion = CajaSesion::create([
                'caja_id' => $caja->id,
                'user_apertura_id' => $userId,
                'fecha_apertura' => now(),
                'monto_apertura' => round($montoApertura, 2),
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'estado' => 'abierta',
                'observaciones' => $observaciones,
            ]);

            AuditoriaService::registrar('caja', 'apertura', $sesion, null, ['monto_apertura' => $sesion->monto_apertura], $caja->tienda_id);

            return $sesion;
        });
    }

    public function registrarMovimiento(CajaSesion $sesion, string $tipo, string $concepto, float $monto, int $metodoPagoId, ?Model $referencia = null, ?int $userId = null): CajaMovimiento
    {
        $monto = round($monto, 2);
        if ($monto <= 0) {
            throw new OperacionException('El monto del movimiento debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($sesion, $tipo, $concepto, $monto, $metodoPagoId, $referencia, $userId) {
            $sesion = CajaSesion::lockForUpdate()->findOrFail($sesion->id);

            if ($sesion->estado !== 'abierta') {
                throw new OperacionException('La caja está cerrada.');
            }

            $movimiento = CajaMovimiento::create([
                'caja_sesion_id' => $sesion->id,
                'tipo' => $tipo,
                'concepto' => $concepto,
                'monto' => $monto,
                'metodo_pago_id' => $metodoPagoId,
                'referencia_type' => $referencia?->getMorphClass(),
                'referencia_id' => $referencia?->getKey(),
                'user_id' => $userId,
                'fecha' => now(),
            ]);

            $sesion->increment($tipo === 'ingreso' ? 'total_ingresos' : 'total_egresos', $monto);

            return $movimiento;
        });
    }

    /** Totales por método de pago: [metodo_id => ['ingreso' => x, 'egreso' => y]] */
    public function totalesPorMetodo(CajaSesion $sesion): array
    {
        $filas = CajaMovimiento::where('caja_sesion_id', $sesion->id)
            ->selectRaw('metodo_pago_id, tipo, SUM(monto) as total')
            ->groupBy('metodo_pago_id', 'tipo')
            ->get();

        $res = [];
        foreach ($filas as $f) {
            $res[$f->metodo_pago_id][$f->tipo] = (float) $f->total;
        }

        return $res;
    }

    /** Efectivo que debería haber físicamente en la caja. */
    public function esperadoEfectivo(CajaSesion $sesion): float
    {
        $efectivo = $this->metodoEfectivoId();

        $suma = fn (string $tipo) => (float) CajaMovimiento::where('caja_sesion_id', $sesion->id)
            ->where('tipo', $tipo)
            ->when($efectivo, fn ($q) => $q->where('metodo_pago_id', $efectivo))
            ->sum('monto');

        return round($sesion->monto_apertura + $suma('ingreso') - $suma('egreso'), 2);
    }

    /** $montoReal null = cierre automático (se asume que el arqueo coincide). */
    public function cerrar(CajaSesion $sesion, ?float $montoReal, string $tipo, ?int $userId, ?string $observaciones = null): CajaSesion
    {
        return DB::transaction(function () use ($sesion, $montoReal, $tipo, $userId, $observaciones) {
            $s = CajaSesion::lockForUpdate()->findOrFail($sesion->id);

            if ($s->estado !== 'abierta') {
                throw new OperacionException('La caja ya está cerrada.');
            }

            $esperado = $this->esperadoEfectivo($s);
            $real = $montoReal === null ? $esperado : round($montoReal, 2);
            $nota = trim(($s->observaciones ? $s->observaciones . ' | ' : '') . ($observaciones ?? ''));

            $s->update([
                'fecha_cierre' => now(),
                'user_cierre_id' => $userId,
                'monto_esperado' => $esperado,
                'monto_real' => $real,
                'diferencia' => round($real - $esperado, 2),
                'tipo_cierre' => $tipo,
                'estado' => 'cerrada',
                'observaciones' => $nota !== '' ? $nota : null,
            ]);

            AuditoriaService::registrar(
                'caja',
                $tipo === 'automatico' ? 'cierre_caja_automatico' : 'cierre_caja',
                $s,
                null,
                ['esperado' => $esperado, 'real' => $real, 'diferencia' => $s->diferencia],
                $s->caja->tienda_id
            );

            return $s;
        });
    }
}
