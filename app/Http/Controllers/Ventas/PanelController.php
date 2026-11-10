<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\CajaSesion;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Transferencia;
use App\Models\Venta;
use App\Services\CajaService;
use App\Support\TiendaContext;
use Illuminate\Support\Facades\DB;

/** Panel de control: cada bloque aparece solo si el usuario tiene el permiso correspondiente. */
class PanelController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function index(CajaService $caja)
    {
        $user = auth()->user();
        $ids = TiendaContext::ids();
        $tienda = TiendaContext::actual();
        $d = [];

        if ($user->can('ventas.ver')) {
            $base = fn () => Venta::whereIn('tienda_id', $ids)->where('estado', 'completada')
                ->when(! $user->can('ventas.ver_todas'), fn ($q) => $q->where('vendedor_id', $user->id));

            $d['ventas_hoy'] = (float) $base()->whereDate('fecha', today())->sum('total');
            $d['ventas_hoy_nro'] = $base()->whereDate('fecha', today())->count();
            $d['ventas_mes'] = (float) $base()->whereDate('fecha', '>=', today()->startOfMonth())->sum('total');

            $serie = [];
            foreach (range(6, 0) as $atras) {
                $dia = today()->subDays($atras);
                $serie[] = ['dia' => $dia->format('d/m'), 'total' => (float) $base()->whereDate('fecha', $dia)->sum('total')];
            }
            $d['serie'] = $serie;
            $d['serie_max'] = max(1, max(array_column($serie, 'total')));

            $d['top_productos'] = DB::table('venta_detalles as dt')->join('ventas as v', 'v.id', '=', 'dt.venta_id')->join('productos as p', 'p.id', '=', 'dt.producto_id')
                ->whereIn('v.tienda_id', $ids)->where('v.estado', 'completada')->whereDate('v.fecha', '>=', today()->startOfMonth())
                ->when(! $user->can('ventas.ver_todas'), fn ($q) => $q->where('v.vendedor_id', $user->id))
                ->selectRaw('p.nombre, SUM(dt.cantidad) as cant, SUM(dt.subtotal) as total')
                ->groupBy('p.id', 'p.nombre')->orderByDesc('total')->limit(5)->get();
        }

        if ($user->can('inventario.ver')) {
            $bajo = Inventario::with(['producto', 'almacen'])->whereHas('almacen', fn ($q) => $q->whereIn('tienda_id', $ids))
                ->where('stock_minimo', '>', 0)->whereColumn('cantidad', '<=', 'stock_minimo');
            $d['bajo_minimo_nro'] = (clone $bajo)->count();
            $d['bajo_minimo'] = (clone $bajo)->orderBy('cantidad')->limit(5)->get();

            $lotes = Lote::with(['producto', 'almacen'])->whereHas('almacen', fn ($q) => $q->whereIn('tienda_id', $ids))
                ->where('cantidad_actual', '>', 0)->whereNotNull('fecha_vencimiento')
                ->whereDate('fecha_vencimiento', '<=', today()->addDays(Lote::DIAS_ALERTA)->toDateString());
            $d['lotes_nro'] = (clone $lotes)->count();
            $d['lotes_vencidos'] = (clone $lotes)->whereDate('fecha_vencimiento', '<', today()->toDateString())->count();
            $d['lotes'] = (clone $lotes)->orderBy('fecha_vencimiento')->limit(5)->get();
        }

        foreach (['cxc' => 'cuentas_por_cobrar', 'cxp' => 'cuentas_por_pagar'] as $permiso => $tabla) {
            if ($user->can("{$permiso}.ver")) {
                $abiertas = DB::table($tabla)->whereIn('tienda_id', $ids)->whereNotIn('estado', ['pagada', 'anulada'])->where('saldo', '>', 0);
                $d["{$permiso}_saldo"] = (float) (clone $abiertas)->sum('saldo');
                $d["{$permiso}_vencido"] = (float) (clone $abiertas)->whereDate('fecha_vencimiento', '<', today()->toDateString())->sum('saldo');
                $d["{$permiso}_vencidas_nro"] = (clone $abiertas)->whereDate('fecha_vencimiento', '<', today()->toDateString())->count();
            }
        }

        if ($user->can('caja.ver')) {
            $d['mi_sesion'] = $tienda ? $caja->sesionAbiertaDeUsuario($user->id, $tienda->id) : null;
            if ($user->can('caja.ver_todas')) {
                $d['cajas_abiertas'] = CajaSesion::with('caja', 'usuarioApertura')->where('estado', 'abierta')
                    ->whereHas('caja', fn ($q) => $q->whereIn('tienda_id', $ids))->get();
            }
        }

        if ($user->can('transferencias.ver')) {
            $d['transferencias_en_transito'] = Transferencia::with(['almacenOrigen', 'almacenDestino'])
                ->where('estado', 'en_transito')->whereIn('tienda_destino_id', $ids)->orderBy('fecha')->limit(5)->get();
        }

        $breadcrumb = $this->breadcrumb(['Panel de control' => route('panel.index')]);

        return view('ventas.panel.index', ['d' => $d, 'tienda' => $tienda, 'breadcrumb' => $breadcrumb]);
    }
}
