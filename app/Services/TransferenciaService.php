<?php

namespace App\Services;

use App\Exceptions\OperacionException;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\Transferencia;
use App\Models\TransferenciaDetalle;
use App\Models\User;
use App\Support\TiendaContext;
use Illuminate\Support\Facades\DB;

/** Flujo: pendiente -> en_transito (sale del origen) -> recibida (entra al destino). */
class TransferenciaService
{
    public function __construct(private InventarioService $inventario, private SecuenciaService $secuencias)
    {
    }

    /** $d: almacen_origen_id, almacen_destino_id, observaciones?, items[]{producto_id,cantidad} */
    public function crear(array $d, User $user): Transferencia
    {
        return DB::transaction(function () use ($d, $user) {
            $origen = Almacen::with('tienda')->find($d['almacen_origen_id'] ?? 0);
            $destino = Almacen::with('tienda')->find($d['almacen_destino_id'] ?? 0);

            if (! $origen || ! $destino || $origen->id === $destino->id) {
                throw new OperacionException('Selecciona un almacén de origen y otro distinto de destino.');
            }
            if (! in_array($origen->tienda_id, TiendaContext::ids(), true)) {
                throw new OperacionException('No tienes acceso al almacén de origen.');
            }
            if ($origen->tienda->empresa_id !== $destino->tienda->empresa_id) {
                throw new OperacionException('El destino debe pertenecer a la misma empresa.');
            }

            $porProducto = [];
            foreach ($d['items'] ?? [] as $i) {
                $pid = (int) ($i['producto_id'] ?? 0);
                $cantidad = round((float) ($i['cantidad'] ?? 0), 3);
                if ($pid && $cantidad > 0) {
                    $porProducto[$pid] = round(($porProducto[$pid] ?? 0) + $cantidad, 3);
                }
            }
            if (! $porProducto) {
                throw new OperacionException('Agrega al menos un producto con cantidad.');
            }

            $validos = Producto::where('empresa_id', $origen->tienda->empresa_id)->where('estado', 1)
                ->whereIn('id', array_keys($porProducto))->pluck('id')->all();
            if (count($validos) !== count($porProducto)) {
                throw new OperacionException('Hay productos inválidos o inactivos en el detalle.');
            }

            $t = Transferencia::create([
                'empresa_id' => $origen->tienda->empresa_id,
                'numero' => $this->secuencias->siguiente($origen->tienda_id, 'transferencia', 'T'),
                'tipo' => $origen->tienda_id === $destino->tienda_id ? 'entre_almacenes' : 'entre_sucursales',
                'tienda_origen_id' => $origen->tienda_id,
                'almacen_origen_id' => $origen->id,
                'tienda_destino_id' => $destino->tienda_id,
                'almacen_destino_id' => $destino->id,
                'user_envia_id' => $user->id,
                'fecha' => today()->toDateString(),
                'estado' => 'pendiente',
                'observaciones' => $d['observaciones'] ?? null,
            ]);

            foreach ($porProducto as $pid => $cantidad) {
                TransferenciaDetalle::create(['transferencia_id' => $t->id, 'producto_id' => $pid, 'cantidad' => $cantidad, 'cantidad_recibida' => 0]);
            }

            AuditoriaService::registrar('transferencias', 'crear', $t, null, ['numero' => $t->numero, 'tipo' => $t->tipo], $origen->tienda_id);

            return $t;
        });
    }

    /** Descuenta el stock del origen (FEFO) y deja un detalle por lote. */
    public function enviar(Transferencia $transferencia, User $user): Transferencia
    {
        return DB::transaction(function () use ($transferencia, $user) {
            $t = Transferencia::with('detalles')->lockForUpdate()->findOrFail($transferencia->id);
            if ($t->estado !== 'pendiente') {
                throw new OperacionException('Solo se pueden enviar transferencias pendientes.');
            }
            $this->exigirAccesoTienda($t->tienda_origen_id);

            $porProducto = $t->detalles->groupBy('producto_id')->map(fn ($g) => (float) $g->sum('cantidad'));
            $t->detalles()->delete();

            foreach ($porProducto as $productoId => $cantidad) {
                $movs = $this->inventario->salida($t->almacen_origen_id, (int) $productoId, $cantidad, 'transferencia_salida', [
                    'referencia' => $t,
                    'observacion' => "Transferencia {$t->numero}",
                ]);
                foreach ($movs as $m) {
                    TransferenciaDetalle::create([
                        'transferencia_id' => $t->id,
                        'producto_id' => $productoId,
                        'lote_id' => $m->lote_id,
                        'cantidad' => abs($m->cantidad),
                        'cantidad_recibida' => 0,
                    ]);
                }
            }

            $t->update(['estado' => 'en_transito', 'user_envia_id' => $user->id]);
            AuditoriaService::registrar('transferencias', 'enviar', $t, null, ['numero' => $t->numero], $t->tienda_origen_id);

            return $t;
        });
    }

    public function recibir(Transferencia $transferencia, User $user): Transferencia
    {
        return DB::transaction(function () use ($transferencia, $user) {
            $t = Transferencia::with('detalles.lote')->lockForUpdate()->findOrFail($transferencia->id);
            if ($t->estado !== 'en_transito') {
                throw new OperacionException('Solo se pueden recibir transferencias en tránsito.');
            }
            $this->exigirAccesoTienda($t->tienda_destino_id);

            foreach ($t->detalles as $d) {
                $this->ingresar($t->almacen_destino_id, $d, $t, 'transferencia_entrada', "Transferencia {$t->numero}");
                $d->update(['cantidad_recibida' => $d->cantidad]);
            }

            $t->update(['estado' => 'recibida', 'user_recibe_id' => $user->id, 'fecha_recepcion' => now()]);
            AuditoriaService::registrar('transferencias', 'recibir', $t, null, ['numero' => $t->numero], $t->tienda_destino_id);

            return $t;
        });
    }

    public function anular(Transferencia $transferencia, User $user): Transferencia
    {
        return DB::transaction(function () use ($transferencia, $user) {
            $t = Transferencia::with('detalles.lote')->lockForUpdate()->findOrFail($transferencia->id);
            if (! in_array($t->estado, ['pendiente', 'en_transito'])) {
                throw new OperacionException('Solo se pueden anular transferencias pendientes o en tránsito.');
            }
            $this->exigirAccesoTienda($t->tienda_origen_id);

            if ($t->estado === 'en_transito') {
                foreach ($t->detalles as $d) {
                    $this->ingresar($t->almacen_origen_id, $d, $t, 'transferencia_entrada', "Anulación transferencia {$t->numero}");
                }
            }

            $t->update(['estado' => 'anulada']);
            AuditoriaService::registrar('transferencias', 'anular', $t, null, ['numero' => $t->numero], $t->tienda_origen_id);

            return $t;
        });
    }

    private function ingresar(int $almacenId, TransferenciaDetalle $d, Transferencia $t, string $tipo, string $obs): void
    {
        $opts = ['referencia' => $t, 'observacion' => $obs];
        if ($d->lote) {
            $opts['numero_lote'] = $d->lote->numero_lote;
            $opts['fecha_vencimiento'] = $d->lote->fecha_vencimiento?->toDateString();
            $opts['costo_unitario'] = $d->lote->costo_unitario;
        }
        $this->inventario->entrada($almacenId, $d->producto_id, (float) $d->cantidad, $tipo, $opts);
    }

    private function exigirAccesoTienda(int $tiendaId): void
    {
        if (! in_array($tiendaId, TiendaContext::ids(), true)) {
            throw new OperacionException('No tienes acceso a la tienda de esta operación.');
        }
    }
}
