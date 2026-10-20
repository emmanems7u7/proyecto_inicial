<?php

namespace App\Services;

use App\Exceptions\InventarioException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Único punto de entrada para mover stock. Ventas, compras, transferencias
 * y devoluciones (fase 2) deben usar entrada()/salida() para que el stock,
 * los lotes y el kardex siempre queden consistentes.
 *
 * $opts admitidos:
 *   costo_unitario, numero_lote, fecha_vencimiento, lote_id, motivo_id,
 *   referencia (Model), user_id, observacion, fecha, incluir_vencidos (bool)
 */
class InventarioService
{
    private const EPS = 0.0005;

    public function entrada(int $almacenId, int $productoId, float $cantidad, string $tipo = 'entrada', array $opts = []): Collection
    {
        return $this->registrar($tipo, 1, $almacenId, $productoId, $cantidad, $opts);
    }

    public function salida(int $almacenId, int $productoId, float $cantidad, string $tipo = 'salida', array $opts = []): Collection
    {
        return $this->registrar($tipo, -1, $almacenId, $productoId, $cantidad, $opts);
    }

    /** @return Collection<int, MovimientoInventario> */
    public function registrar(string $tipo, int $signo, int $almacenId, int $productoId, float $cantidad, array $opts = []): Collection
    {
        $cantidad = round($cantidad, 3);
        if ($cantidad <= 0) {
            throw new InventarioException('La cantidad debe ser mayor a cero.');
        }

        return DB::transaction(function () use ($tipo, $signo, $almacenId, $productoId, $cantidad, $opts) {
            $almacen = Almacen::findOrFail($almacenId);
            $producto = Producto::findOrFail($productoId);

            Inventario::firstOrCreate(
                ['almacen_id' => $almacenId, 'producto_id' => $productoId],
                ['cantidad' => 0, 'stock_minimo' => 0]
            );
            $inventario = Inventario::where('almacen_id', $almacenId)
                ->where('producto_id', $productoId)
                ->lockForUpdate()
                ->firstOrFail();

            return $signo > 0
                ? collect([$this->ingresar($inventario, $producto, $almacen, $tipo, $cantidad, $opts)])
                : $this->egresar($inventario, $producto, $almacen, $tipo, $cantidad, $opts);
        });
    }

    private function ingresar(Inventario $inventario, Producto $producto, Almacen $almacen, string $tipo, float $cantidad, array $opts): MovimientoInventario
    {
        $lote = null;

        if ($producto->maneja_lotes) {
            if ($producto->maneja_vencimiento && empty($opts['fecha_vencimiento'])) {
                throw new InventarioException("«{$producto->nombre}» requiere fecha de vencimiento para el lote.");
            }

            $numero = trim((string) ($opts['numero_lote'] ?? '')) ?: 'L' . now()->format('ymdHis');

            $lote = Lote::where('producto_id', $producto->id)
                ->where('almacen_id', $almacen->id)
                ->where('numero_lote', $numero)
                ->lockForUpdate()
                ->first();

            if ($lote) {
                $lote->cantidad_actual = round($lote->cantidad_actual + $cantidad, 3);
                $lote->cantidad_inicial = round($lote->cantidad_inicial + $cantidad, 3);
                if (! $lote->fecha_vencimiento && ! empty($opts['fecha_vencimiento'])) {
                    $lote->fecha_vencimiento = $opts['fecha_vencimiento'];
                }
                $lote->estado = 1;
                $lote->save();
            } else {
                $lote = Lote::create([
                    'producto_id' => $producto->id,
                    'almacen_id' => $almacen->id,
                    'numero_lote' => $numero,
                    'fecha_ingreso' => now()->toDateString(),
                    'fecha_vencimiento' => $opts['fecha_vencimiento'] ?? null,
                    'cantidad_inicial' => $cantidad,
                    'cantidad_actual' => $cantidad,
                    'costo_unitario' => $opts['costo_unitario'] ?? $producto->precio_compra,
                    'estado' => 1,
                ]);
            }
        }

        $saldoAnterior = $inventario->cantidad;
        $inventario->cantidad = round($saldoAnterior + $cantidad, 3);
        $this->normalizarAlerta($inventario);
        $inventario->save();

        return $this->kardex($tipo, $almacen, $producto, $lote, $cantidad, $saldoAnterior, $inventario->cantidad, $opts);
    }

    /** @return Collection<int, MovimientoInventario> */
    private function egresar(Inventario $inventario, Producto $producto, Almacen $almacen, string $tipo, float $cantidad, array $opts): Collection
    {
        $saldo = $inventario->cantidad;

        if ($cantidad > $saldo + self::EPS) {
            throw new StockInsuficienteException(
                "Stock insuficiente de «{$producto->nombre}» en {$almacen->nombre}: disponible " . $this->fmt($saldo) . ', solicitado ' . $this->fmt($cantidad) . '.'
            );
        }

        $movimientos = collect();

        if (! $producto->maneja_lotes) {
            $nuevo = round($saldo - $cantidad, 3);
            $movimientos->push($this->kardex($tipo, $almacen, $producto, null, -$cantidad, $saldo, $nuevo, $opts));
            $saldo = $nuevo;
        } else {
            $consulta = Lote::where('producto_id', $producto->id)
                ->where('almacen_id', $almacen->id)
                ->where('cantidad_actual', '>', 0)
                ->lockForUpdate();

            if (! empty($opts['lote_id'])) {
                $consulta->where('id', $opts['lote_id']);
            } elseif (empty($opts['incluir_vencidos'])) {
                $hoy = today()->toDateString();
                $consulta->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', $hoy));
            }

            // FEFO: primero los que vencen antes; los sin vencimiento al final.
            $lotes = $consulta->orderByRaw('fecha_vencimiento IS NULL')->orderBy('fecha_vencimiento')->orderBy('id')->get();

            $disponible = round((float) $lotes->sum('cantidad_actual'), 3);
            if ($disponible + self::EPS < $cantidad) {
                throw new StockInsuficienteException(
                    "Stock en lotes utilizables insuficiente de «{$producto->nombre}»: disponible " . $this->fmt($disponible) . ', solicitado ' . $this->fmt($cantidad) . '. Revisa lotes vencidos.'
                );
            }

            $restante = $cantidad;
            foreach ($lotes as $lote) {
                if ($restante <= self::EPS) {
                    break;
                }
                $toma = round(min($restante, $lote->cantidad_actual), 3);

                $lote->cantidad_actual = round($lote->cantidad_actual - $toma, 3);
                if ($lote->cantidad_actual <= self::EPS) {
                    $lote->cantidad_actual = 0;
                    $lote->estado = 2; // agotado
                }
                $lote->save();

                $nuevo = round($saldo - $toma, 3);
                $movimientos->push($this->kardex($tipo, $almacen, $producto, $lote, -$toma, $saldo, $nuevo, $opts));
                $saldo = $nuevo;
                $restante = round($restante - $toma, 3);
            }
        }

        $inventario->cantidad = $saldo;
        $this->normalizarAlerta($inventario);
        $inventario->save();

        return $movimientos;
    }

    private function kardex(string $tipo, Almacen $almacen, Producto $producto, ?Lote $lote, float $cantidadConSigno, float $saldoAnterior, float $saldoPosterior, array $opts): MovimientoInventario
    {
        $referencia = $opts['referencia'] ?? null;

        return MovimientoInventario::create([
            'tienda_id' => $almacen->tienda_id,
            'almacen_id' => $almacen->id,
            'producto_id' => $producto->id,
            'lote_id' => $lote?->id,
            'tipo' => $tipo,
            'cantidad' => $cantidadConSigno,
            'saldo_anterior' => $saldoAnterior,
            'saldo_posterior' => $saldoPosterior,
            'costo_unitario' => $opts['costo_unitario'] ?? ($lote?->costo_unitario ?? $producto->precio_compra),
            'motivo_id' => $opts['motivo_id'] ?? null,
            'referencia_type' => $referencia instanceof Model ? $referencia->getMorphClass() : null,
            'referencia_id' => $referencia instanceof Model ? $referencia->getKey() : null,
            'user_id' => $opts['user_id'] ?? auth()->id(),
            'observacion' => $opts['observacion'] ?? null,
            'fecha' => $opts['fecha'] ?? now(),
        ]);
    }

    /** Si el stock vuelve a superar el mínimo, se permite una nueva alerta futura. */
    private function normalizarAlerta(Inventario $inventario): void
    {
        if ($inventario->cantidad > $inventario->stock_minimo) {
            $inventario->alerta_stock_enviada_at = null;
        }
    }

    private function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 3, '.', ','), '0'), '.');
    }
}
