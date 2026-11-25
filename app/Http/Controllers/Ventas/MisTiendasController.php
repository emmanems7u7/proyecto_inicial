<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\CuentaPorCobrar;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\Tienda;
use App\Models\User;
use App\Models\Venta;
use App\Support\TiendaContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/** Pantalla de entrada: tiendas / sucursales disponibles para el usuario, con sus indicadores del día. */
class MisTiendasController extends Controller
{
    use UsaContexto;

    public function index()
    {
        $tiendas = TiendaContext::permitidas();
        $ids = $tiendas->pluck('id')->all();

        $ventasHoy = Venta::whereIn('tienda_id', $ids)->where('estado', 'completada')->whereDate('fecha', today())
            ->selectRaw('tienda_id, SUM(total) as t')->groupBy('tienda_id')->pluck('t', 'tienda_id');

        $productos = Inventario::join('almacenes', 'almacenes.id', '=', 'inventarios.almacen_id')
            ->whereIn('almacenes.tienda_id', $ids)->where('inventarios.cantidad', '>', 0)
            ->selectRaw('almacenes.tienda_id as tienda_id, COUNT(DISTINCT inventarios.producto_id) as n')
            ->groupBy('almacenes.tienda_id')->pluck('n', 'tienda_id');

        $stockBajo = Inventario::join('almacenes', 'almacenes.id', '=', 'inventarios.almacen_id')
            ->whereIn('almacenes.tienda_id', $ids)->where('inventarios.stock_minimo', '>', 0)
            ->whereColumn('inventarios.cantidad', '<=', 'inventarios.stock_minimo')
            ->selectRaw('almacenes.tienda_id as tienda_id, COUNT(*) as n')->groupBy('almacenes.tienda_id')->pluck('n', 'tienda_id');

        $porVencer = Lote::join('almacenes', 'almacenes.id', '=', 'lotes.almacen_id')
            ->whereIn('almacenes.tienda_id', $ids)->where('lotes.cantidad_actual', '>', 0)
            ->whereNotNull('lotes.fecha_vencimiento')->whereDate('lotes.fecha_vencimiento', '<=', today()->addDays(30))
            ->selectRaw('almacenes.tienda_id as tienda_id, COUNT(*) as n')->groupBy('almacenes.tienda_id')->pluck('n', 'tienda_id');

        $cxc = CuentaPorCobrar::whereIn('tienda_id', $ids)->whereNotIn('estado', ['pagada', 'anulada'])
            ->selectRaw('tienda_id, SUM(saldo) as s')->groupBy('tienda_id')->pluck('s', 'tienda_id');

        $tarjetas = $tiendas->map(function (Tienda $t) use ($ventasHoy, $productos, $stockBajo, $porVencer) {
            $admin = $t->usuarios()->role('administrador_tienda')->first();
            $alerta = ($porVencer[$t->id] ?? 0) > 0 ? ($porVencer[$t->id] . ' por vencer')
                : (($stockBajo[$t->id] ?? 0) > 0 ? ($stockBajo[$t->id] . ' stock bajo') : null);

            return [
                'tienda' => $t,
                'ventas_hoy' => (float) ($ventasHoy[$t->id] ?? 0),
                'productos' => (int) ($productos[$t->id] ?? 0),
                'alerta' => $alerta,
                'admin' => $admin?->nombre_completo ?: '—',
            ];
        });

        $resumen = [
            'ventas_hoy' => (float) $ventasHoy->sum(),
            'usuarios' => User::where('empresa_id', $this->empresaId())->count(),
            'cxc' => (float) $cxc->sum(),
        ];
        $user = auth()->user();
        $puedeCrear = $user->can('tiendas.crear');
        $puedeReportes = $user->can('reportes.globales') && Route::has('reportes.globales');
        $breadcrumb = $this->breadcrumb(['Mis tiendas' => null]);

        return view('ventas.mis_tiendas.index', compact('tarjetas', 'resumen', 'puedeCrear', 'puedeReportes', 'breadcrumb'));
    }

    /** Elige la tienda y entra al sistema (panel de control si el usuario lo tiene). */
    public function entrar(Tienda $tienda)
    {
        abort_unless(TiendaContext::establecer($tienda->id), 403, 'No tienes acceso a esa tienda.');
        $user = auth()->user();
        $destino = (Route::has('panel.index') && $user->can('panel.ver')) ? route('panel.index') : route('home');

        return redirect($destino)->with('success', 'Tienda activa: ' . $tienda->nombre);
    }
}
