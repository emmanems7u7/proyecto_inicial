<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\Acceso;
use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\User;
use App\Support\Formato;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reportes. Cada método arma cabeceras + filas crudas y delega en salida(),
 * que muestra la tabla o exporta CSV (abre en Excel) con ?export=csv.
 * Tipos de columna: texto | dinero | cantidad | entero | fecha
 */
class ReporteController extends Controller
{
    use UsaContexto, UsaOperaciones;

    private const NOMBRE_USUARIO = "CONCAT_WS(' ', u.usuario_nombres, u.usuario_app, u.usuario_apm)";

    /* ---------------- Ventas ---------------- */
    public function ventas(Request $request)
    {
        [$desde, $hasta] = $this->fechas($request);
        $agrupar = $request->input('agrupar', 'dia');
        $ids = TiendaContext::ids();
        $empresaId = $this->empresaId();

        $filtros = fn ($q) => $q->whereIn('v.tienda_id', $ids)->where('v.estado', 'completada')
            ->whereDate('v.fecha', '>=', $desde)->whereDate('v.fecha', '<=', $hasta)
            ->when($request->filled('tienda_id'), fn ($x) => $x->where('v.tienda_id', $request->input('tienda_id')))
            ->when($request->filled('vendedor_id'), fn ($x) => $x->where('v.vendedor_id', $request->input('vendedor_id')))
            ->when($request->filled('cliente_id'), fn ($x) => $x->where('v.cliente_id', $request->input('cliente_id')));

        if ($agrupar === 'producto') {
            $filas = $filtros(DB::table('venta_detalles as d')->join('ventas as v', 'v.id', '=', 'd.venta_id')->join('productos as p', 'p.id', '=', 'd.producto_id'))
                ->selectRaw('p.codigo, p.nombre, SUM(d.cantidad) as cant, SUM(d.subtotal) as venta, SUM(d.cantidad * d.costo_unitario) as costo')
                ->groupBy('p.id', 'p.codigo', 'p.nombre')->orderByDesc('venta')->get()
                ->map(fn ($r) => [$r->codigo, $r->nombre, $r->cant, $r->venta, $r->costo, $r->venta - $r->costo])->all();
            $cabeceras = ['Código', 'Producto', 'Cantidad', 'Ventas (Bs)', 'Costo (Bs)', 'Utilidad (Bs)'];
            $tipos = ['texto', 'texto', 'cantidad', 'dinero', 'dinero', 'dinero'];
            $sumar = [2, 3, 4, 5];
        } else {
            $costos = DB::table('venta_detalles')->selectRaw('venta_id, SUM(cantidad * costo_unitario) as costo')->groupBy('venta_id');
            $q = $filtros(DB::table('ventas as v'))->leftJoinSub($costos, 'c', 'c.venta_id', '=', 'v.id');

            [$etiqueta, $titulo] = match ($agrupar) {
                'tienda' => [$q->join('tiendas as t', 't.id', '=', 'v.tienda_id'), 't.nombre'],
                'vendedor' => [$q->join('users as u', 'u.id', '=', 'v.vendedor_id'), self::NOMBRE_USUARIO],
                'cliente' => [$q->leftJoin('clientes as cl', 'cl.id', '=', 'v.cliente_id'), "COALESCE(cl.nombre, 'Consumidor final')"],
                'forma_pago' => [$q, 'v.tipo_pago'],
                default => [$q, 'DATE(v.fecha)'],
            };
            $filas = $etiqueta
                ->selectRaw("{$titulo} as etiqueta, COUNT(v.id) as nro, SUM(v.total) as total, SUM(v.pagado) as pagado, SUM(v.saldo) as saldo, SUM(v.total - COALESCE(c.costo, 0)) as utilidad")
                ->groupByRaw($titulo . ($agrupar === 'vendedor' ? ', u.id' : ''))
                ->orderByRaw($agrupar === 'dia' ? 'etiqueta' : 'total DESC')->get()
                ->map(fn ($r) => [$r->etiqueta, $r->nro, $r->total, $r->pagado, $r->saldo, $r->utilidad])->all();
            $cabeceras = [ucfirst(str_replace('_', ' ', $agrupar)), 'N.º ventas', 'Total (Bs)', 'Cobrado (Bs)', 'Saldo (Bs)', 'Utilidad bruta (Bs)'];
            $tipos = ['texto', 'entero', 'dinero', 'dinero', 'dinero', 'dinero'];
            $sumar = [1, 2, 3, 4, 5];
        }

        $devoluciones = (float) DB::table('devoluciones_venta')->whereIn('tienda_id', $ids)
            ->whereDate('fecha', '>=', $desde)->whereDate('fecha', '<=', $hasta)
            ->when($request->filled('tienda_id'), fn ($x) => $x->where('tienda_id', $request->input('tienda_id')))->sum('total');

        $filtrosUi = [
            $this->fDate('desde', 'Desde', $desde), $this->fDate('hasta', 'Hasta', $hasta),
            $this->fSelect('tienda_id', 'Tienda', TiendaContext::permitidas()->pluck('nombre', 'id')->all(), 'Todas'),
            $this->fSelect('vendedor_id', 'Vendedor', User::where('empresa_id', $empresaId)->orderBy('usuario_nombres')->get()->pluck('nombre_completo', 'id')->all(), 'Todos'),
            $this->fSelect('cliente_id', 'Cliente', Cliente::where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all(), 'Todos'),
            $this->fSelect('agrupar', 'Agrupar por', ['dia' => 'Día', 'tienda' => 'Tienda', 'vendedor' => 'Vendedor', 'cliente' => 'Cliente', 'producto' => 'Producto', 'forma_pago' => 'Forma de pago'], null, $agrupar),
        ];

        return $this->salida($request, 'Reporte de ventas', 'reportes.ventas', $filtrosUi, $cabeceras, $tipos, $filas, $sumar,
            ['Devoluciones del período' => 'Bs ' . Formato::dinero($devoluciones)]);
    }

    /* ---------------- Inventario ---------------- */
    public function inventario(Request $request)
    {
        $tipo = $request->input('tipo', 'stock');
        $ids = TiendaContext::ids();
        $empresaId = $this->empresaId();

        if ($tipo === 'vencimientos') {
            $dias = (int) $request->input('dias', 60);
            $filas = DB::table('lotes as l')
                ->join('almacenes as a', 'a.id', '=', 'l.almacen_id')->join('tiendas as t', 't.id', '=', 'a.tienda_id')
                ->join('productos as p', 'p.id', '=', 'l.producto_id')
                ->whereIn('a.tienda_id', $ids)->where('p.empresa_id', $empresaId)
                ->where('l.cantidad_actual', '>', 0)->whereNotNull('l.fecha_vencimiento')
                ->whereDate('l.fecha_vencimiento', '<=', today()->addDays($dias)->toDateString())
                ->when($request->filled('tienda_id'), fn ($x) => $x->where('a.tienda_id', $request->input('tienda_id')))
                ->orderBy('l.fecha_vencimiento')->limit(3000)
                ->get(['t.nombre as tienda', 'a.nombre as almacen', 'p.codigo', 'p.nombre as producto', 'l.numero_lote', 'l.fecha_vencimiento', 'l.cantidad_actual', 'l.costo_unitario'])
                ->map(fn ($r) => [$r->tienda, $r->almacen, $r->codigo . ' - ' . $r->producto, $r->numero_lote, $r->fecha_vencimiento,
                    (int) today()->diffInDays(\Illuminate\Support\Carbon::parse($r->fecha_vencimiento), false), $r->cantidad_actual, $r->cantidad_actual * $r->costo_unitario])->all();
            $cabeceras = ['Tienda', 'Almacén', 'Producto', 'Lote', 'Vence', 'Días', 'Cantidad', 'Valor (Bs)'];
            $tipos = ['texto', 'texto', 'texto', 'texto', 'fecha', 'entero', 'cantidad', 'dinero'];
            $sumar = [6, 7];
        } else {
            $consulta = DB::table('inventarios as i')
                ->join('almacenes as a', 'a.id', '=', 'i.almacen_id')->join('tiendas as t', 't.id', '=', 'a.tienda_id')
                ->join('productos as p', 'p.id', '=', 'i.producto_id')
                ->leftJoin('categorias_producto as c', 'c.id', '=', 'p.categoria_producto_id')
                ->whereIn('a.tienda_id', $ids)->where('p.empresa_id', $empresaId)->whereNull('p.deleted_at')
                ->when($request->filled('tienda_id'), fn ($x) => $x->where('a.tienda_id', $request->input('tienda_id')))
                ->when($request->filled('categoria_producto_id'), fn ($x) => $x->where('p.categoria_producto_id', $request->input('categoria_producto_id')))
                ->when($tipo === 'bajo_minimo', fn ($x) => $x->where('i.stock_minimo', '>', 0)->whereColumn('i.cantidad', '<=', 'i.stock_minimo'))
                ->when($tipo !== 'bajo_minimo', fn ($x) => $x->where('i.cantidad', '>', 0))
                ->orderBy('t.nombre')->orderBy('a.nombre')->orderBy('p.nombre')->limit(5000);

            $datos = $consulta->get(['t.nombre as tienda', 'a.nombre as almacen', 'p.codigo', 'p.nombre as producto', 'c.nombre as categoria', 'i.cantidad', 'i.stock_minimo', 'p.precio_compra']);

            if ($tipo === 'valorizado') {
                $filas = $datos->map(fn ($r) => [$r->tienda, $r->almacen, $r->codigo . ' - ' . $r->producto, $r->categoria ?? '—', $r->cantidad, $r->precio_compra, $r->cantidad * $r->precio_compra])->all();
                $cabeceras = ['Tienda', 'Almacén', 'Producto', 'Categoría', 'Cantidad', 'Costo ref. (Bs)', 'Valor (Bs)'];
                $tipos = ['texto', 'texto', 'texto', 'texto', 'cantidad', 'dinero', 'dinero'];
                $sumar = [6];
            } else {
                $filas = $datos->map(fn ($r) => [$r->tienda, $r->almacen, $r->codigo . ' - ' . $r->producto, $r->categoria ?? '—', $r->cantidad, $r->stock_minimo])->all();
                $cabeceras = ['Tienda', 'Almacén', 'Producto', 'Categoría', 'Stock', 'Mínimo'];
                $tipos = ['texto', 'texto', 'texto', 'texto', 'cantidad', 'cantidad'];
                $sumar = [];
            }
        }

        $filtrosUi = [
            $this->fSelect('tipo', 'Reporte', ['stock' => 'Stock por tienda', 'valorizado' => 'Inventario valorizado', 'bajo_minimo' => 'Por agotarse (bajo mínimo)', 'vencimientos' => 'Lotes por vencer / vencidos'], null, $tipo),
            $this->fSelect('tienda_id', 'Tienda', TiendaContext::permitidas()->pluck('nombre', 'id')->all(), 'Todas'),
            $this->fSelect('categoria_producto_id', 'Categoría', \App\Models\CategoriaProducto::where('empresa_id', $empresaId)->orderBy('nombre')->pluck('nombre', 'id')->all(), 'Todas'),
        ];
        if ($tipo === 'vencimientos') {
            $filtrosUi[] = ['name' => 'dias', 'label' => 'Vence en (días)', 'type' => 'number', 'value' => $request->input('dias', 60)];
        }

        return $this->salida($request, 'Reporte de inventario', 'reportes.inventario', $filtrosUi, $cabeceras, $tipos, $filas, $sumar);
    }

    /* ---------------- Compras ---------------- */
    public function compras(Request $request)
    {
        [$desde, $hasta] = $this->fechas($request);
        $agrupar = $request->input('agrupar', 'proveedor');
        $ids = TiendaContext::ids();

        $filtros = fn ($q) => $q->whereIn('c.tienda_id', $ids)->where('c.estado', 'recibida')
            ->whereDate('c.fecha', '>=', $desde)->whereDate('c.fecha', '<=', $hasta)
            ->when($request->filled('tienda_id'), fn ($x) => $x->where('c.tienda_id', $request->input('tienda_id')))
            ->when($request->filled('proveedor_id'), fn ($x) => $x->where('c.proveedor_id', $request->input('proveedor_id')));

        if ($agrupar === 'producto') {
            $filas = $filtros(DB::table('compra_detalles as d')->join('compras as c', 'c.id', '=', 'd.compra_id')->join('productos as p', 'p.id', '=', 'd.producto_id'))
                ->selectRaw('p.codigo, p.nombre, SUM(d.cantidad) as cant, SUM(d.subtotal) as importe')
                ->groupBy('p.id', 'p.codigo', 'p.nombre')->orderByDesc('importe')->get()
                ->map(fn ($r) => [$r->codigo, $r->nombre, $r->cant, $r->importe, $r->cant > 0 ? $r->importe / $r->cant : 0])->all();
            $cabeceras = ['Código', 'Producto', 'Cantidad', 'Importe (Bs)', 'Costo prom. (Bs)'];
            $tipos = ['texto', 'texto', 'cantidad', 'dinero', 'dinero'];
            $sumar = [2, 3];
        } else {
            [$sel, $q] = match ($agrupar) {
                'dia' => ['DATE(c.fecha)', $filtros(DB::table('compras as c'))],
                default => ['pr.nombre', $filtros(DB::table('compras as c'))->join('proveedores as pr', 'pr.id', '=', 'c.proveedor_id')],
            };
            $filas = $q->selectRaw("{$sel} as etiqueta, COUNT(c.id) as nro, SUM(c.total) as total, SUM(c.pagado) as pagado, SUM(c.saldo) as saldo")
                ->groupByRaw($sel)->orderByRaw($agrupar === 'dia' ? 'etiqueta' : 'total DESC')->get()
                ->map(fn ($r) => [$r->etiqueta, $r->nro, $r->total, $r->pagado, $r->saldo])->all();
            $cabeceras = [$agrupar === 'dia' ? 'Día' : 'Proveedor', 'N.º compras', 'Total (Bs)', 'Pagado (Bs)', 'Saldo (Bs)'];
            $tipos = ['texto', 'entero', 'dinero', 'dinero', 'dinero'];
            $sumar = [1, 2, 3, 4];
        }

        $filtrosUi = [
            $this->fDate('desde', 'Desde', $desde), $this->fDate('hasta', 'Hasta', $hasta),
            $this->fSelect('tienda_id', 'Tienda', TiendaContext::permitidas()->pluck('nombre', 'id')->all(), 'Todas'),
            $this->fSelect('proveedor_id', 'Proveedor', Proveedor::where('empresa_id', $this->empresaId())->orderBy('nombre')->pluck('nombre', 'id')->all(), 'Todos'),
            $this->fSelect('agrupar', 'Agrupar por', ['proveedor' => 'Proveedor', 'producto' => 'Producto', 'dia' => 'Día'], null, $agrupar),
        ];

        return $this->salida($request, 'Reporte de compras', 'reportes.compras', $filtrosUi, $cabeceras, $tipos, $filas, $sumar);
    }

    /* ---------------- Cuentas por cobrar / pagar ---------------- */
    public function cuentas(Request $request)
    {
        $tipo = $request->input('tipo', 'cobrar');
        $estado = $request->input('estado', 'abiertas');
        $ids = TiendaContext::ids();
        [$tabla, $terceros, $fk, $etq] = $tipo === 'pagar'
            ? ['cuentas_por_pagar', 'proveedores', 'proveedor_id', 'Proveedor']
            : ['cuentas_por_cobrar', 'clientes', 'cliente_id', 'Cliente'];

        $filas = DB::table("{$tabla} as c")
            ->join("{$terceros} as x", 'x.id', '=', "c.{$fk}")->join('tiendas as t', 't.id', '=', 'c.tienda_id')
            ->whereIn('c.tienda_id', $ids)->whereNotIn('c.estado', ['anulada'])
            ->when($estado === 'abiertas', fn ($q) => $q->where('c.saldo', '>', 0))
            ->when($estado === 'vencidas', fn ($q) => $q->where('c.saldo', '>', 0)->whereDate('c.fecha_vencimiento', '<', today()->toDateString()))
            ->when($request->filled('tienda_id'), fn ($q) => $q->where('c.tienda_id', $request->input('tienda_id')))
            ->orderBy('c.fecha_vencimiento')->limit(5000)
            ->get(['t.nombre as tienda', 'x.nombre as tercero', 'c.numero_documento', 'c.fecha_emision', 'c.fecha_vencimiento', 'c.monto_total', 'c.monto_pagado', 'c.saldo'])
            ->map(function ($r) {
                $dias = max(0, (int) \Illuminate\Support\Carbon::parse($r->fecha_vencimiento)->diffInDays(today(), false));
                return [$r->tienda, $r->tercero, $r->numero_documento, $r->fecha_emision, $r->fecha_vencimiento, $r->saldo > 0 ? $dias : 0, $r->monto_total, $r->monto_pagado, $r->saldo];
            })->all();

        $cabeceras = ['Tienda', $etq, 'Documento', 'Emisión', 'Vence', 'Días vencido', 'Total (Bs)', 'Pagado (Bs)', 'Saldo (Bs)'];
        $tipos = ['texto', 'texto', 'texto', 'fecha', 'fecha', 'entero', 'dinero', 'dinero', 'dinero'];

        $filtrosUi = [
            $this->fSelect('tipo', 'Cuentas', ['cobrar' => 'Por cobrar (clientes)', 'pagar' => 'Por pagar (proveedores)'], null, $tipo),
            $this->fSelect('estado', 'Estado', ['abiertas' => 'Con saldo', 'vencidas' => 'Vencidas', 'todas' => 'Todas'], null, $estado),
            $this->fSelect('tienda_id', 'Tienda', TiendaContext::permitidas()->pluck('nombre', 'id')->all(), 'Todas'),
        ];

        return $this->salida($request, 'Saldos y vencimientos de cuentas', 'reportes.cuentas', $filtrosUi, $cabeceras, $tipos, $filas, [6, 7, 8]);
    }

    /* ---------------- Caja ---------------- */
    public function caja(Request $request)
    {
        [$desde, $hasta] = $this->fechas($request);
        $ids = TiendaContext::ids();

        $filas = DB::table('caja_sesiones as s')
            ->join('cajas as c', 'c.id', '=', 's.caja_id')->join('tiendas as t', 't.id', '=', 'c.tienda_id')
            ->join('users as u', 'u.id', '=', 's.user_apertura_id')
            ->whereIn('c.tienda_id', $ids)
            ->whereDate('s.fecha_apertura', '>=', $desde)->whereDate('s.fecha_apertura', '<=', $hasta)
            ->when($request->filled('tienda_id'), fn ($q) => $q->where('c.tienda_id', $request->input('tienda_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('s.estado', $request->input('estado')))
            ->orderByDesc('s.fecha_apertura')->limit(5000)
            ->selectRaw('t.nombre as tienda, c.nombre as caja, ' . self::NOMBRE_USUARIO . ' as usuario, s.fecha_apertura, s.fecha_cierre, s.monto_apertura, s.total_ingresos, s.total_egresos, s.monto_esperado, s.monto_real, s.diferencia, s.tipo_cierre, s.estado')
            ->get()
            ->map(fn ($r) => [$r->tienda, $r->caja, $r->usuario, $r->fecha_apertura, $r->fecha_cierre ?? '—', $r->monto_apertura, $r->total_ingresos, $r->total_egresos, $r->monto_esperado ?? 0, $r->monto_real ?? 0, $r->diferencia ?? 0, $r->tipo_cierre ?? '—', $r->estado])->all();

        $cabeceras = ['Tienda', 'Caja', 'Usuario', 'Apertura', 'Cierre', 'Monto inicial', 'Ingresos', 'Egresos', 'Esperado', 'Real', 'Diferencia', 'Cierre', 'Estado'];
        $tipos = ['texto', 'texto', 'texto', 'texto', 'texto', 'dinero', 'dinero', 'dinero', 'dinero', 'dinero', 'dinero', 'texto', 'texto'];

        $filtrosUi = [
            $this->fDate('desde', 'Desde', $desde), $this->fDate('hasta', 'Hasta', $hasta),
            $this->fSelect('tienda_id', 'Tienda', TiendaContext::permitidas()->pluck('nombre', 'id')->all(), 'Todas'),
            $this->fSelect('estado', 'Estado', ['abierta' => 'Abiertas', 'cerrada' => 'Cerradas'], 'Todos'),
        ];

        return $this->salida($request, 'Reporte de caja', 'reportes.caja', $filtrosUi, $cabeceras, $tipos, $filas, [6, 7, 10]);
    }

    /* ---------------- Globales (administradores) ---------------- */
    public function globales(Request $request)
    {
        [$desde, $hasta] = $this->fechas($request);
        $tiendas = TiendaContext::permitidas();
        $ids = $tiendas->pluck('id')->all();

        $ventas = DB::table('ventas')->whereIn('tienda_id', $ids)->where('estado', 'completada')
            ->whereDate('fecha', '>=', $desde)->whereDate('fecha', '<=', $hasta)
            ->selectRaw('tienda_id, SUM(total) as total')->groupBy('tienda_id')->pluck('total', 'tienda_id');
        $costos = DB::table('venta_detalles as d')->join('ventas as v', 'v.id', '=', 'd.venta_id')
            ->whereIn('v.tienda_id', $ids)->where('v.estado', 'completada')
            ->whereDate('v.fecha', '>=', $desde)->whereDate('v.fecha', '<=', $hasta)
            ->selectRaw('v.tienda_id, SUM(d.cantidad * d.costo_unitario) as costo')->groupBy('v.tienda_id')->pluck('costo', 'tienda_id');
        $devol = DB::table('devoluciones_venta')->whereIn('tienda_id', $ids)->whereDate('fecha', '>=', $desde)->whereDate('fecha', '<=', $hasta)
            ->selectRaw('tienda_id, SUM(total) as total')->groupBy('tienda_id')->pluck('total', 'tienda_id');
        $compras = DB::table('compras')->whereIn('tienda_id', $ids)->where('estado', 'recibida')
            ->whereDate('fecha', '>=', $desde)->whereDate('fecha', '<=', $hasta)
            ->selectRaw('tienda_id, SUM(total) as total')->groupBy('tienda_id')->pluck('total', 'tienda_id');
        $inventario = DB::table('inventarios as i')->join('almacenes as a', 'a.id', '=', 'i.almacen_id')->join('productos as p', 'p.id', '=', 'i.producto_id')
            ->whereIn('a.tienda_id', $ids)->selectRaw('a.tienda_id, SUM(i.cantidad * p.precio_compra) as valor')->groupBy('a.tienda_id')->pluck('valor', 'tienda_id');
        $cxc = DB::table('cuentas_por_cobrar')->whereIn('tienda_id', $ids)->whereNotIn('estado', ['pagada', 'anulada'])
            ->selectRaw('tienda_id, SUM(saldo) as total')->groupBy('tienda_id')->pluck('total', 'tienda_id');
        $cxp = DB::table('cuentas_por_pagar')->whereIn('tienda_id', $ids)->whereNotIn('estado', ['pagada', 'anulada'])
            ->selectRaw('tienda_id, SUM(saldo) as total')->groupBy('tienda_id')->pluck('total', 'tienda_id');

        $filas = $tiendas->map(function ($t) use ($ventas, $costos, $devol, $compras, $inventario, $cxc, $cxp) {
            $v = (float) ($ventas[$t->id] ?? 0);

            return [$t->nombre, $v, (float) ($devol[$t->id] ?? 0), $v - (float) ($costos[$t->id] ?? 0), (float) ($compras[$t->id] ?? 0),
                (float) ($inventario[$t->id] ?? 0), (float) ($cxc[$t->id] ?? 0), (float) ($cxp[$t->id] ?? 0)];
        })->all();

        $cabeceras = ['Tienda', 'Ventas (Bs)', 'Devoluciones (Bs)', 'Utilidad bruta (Bs)', 'Compras (Bs)', 'Inventario valorizado (Bs)', 'Por cobrar (Bs)', 'Por pagar (Bs)'];
        $tipos = ['texto', 'dinero', 'dinero', 'dinero', 'dinero', 'dinero', 'dinero', 'dinero'];

        return $this->salida($request, 'Reporte global por tienda', 'reportes.globales',
            [$this->fDate('desde', 'Desde', $desde), $this->fDate('hasta', 'Hasta', $hasta)], $cabeceras, $tipos, $filas, [1, 2, 3, 4, 5, 6, 7],
            ['Nota' => 'Inventario, por cobrar y por pagar son saldos a la fecha actual.']);
    }

    /* ---------------- Auditoría y accesos ---------------- */
    public function auditoria(Request $request)
    {
        [$desde, $hasta] = $this->fechas($request, 7);

        $filas = Auditoria::with(['user', 'tienda'])
            ->where(fn ($q) => $q->whereNull('tienda_id')->orWhereIn('tienda_id', TiendaContext::ids()))
            ->whereDate('created_at', '>=', $desde)->whereDate('created_at', '<=', $hasta)
            ->when($request->filled('modulo'), fn ($q) => $q->where('modulo', $request->input('modulo')))
            ->when($request->filled('accion'), fn ($q) => $q->where('accion', 'like', '%' . $request->input('accion') . '%'))
            ->orderByDesc('created_at')->orderByDesc('id')->limit(1000)->get()
            ->map(fn ($a) => [
                $a->created_at->format('d/m/Y H:i:s'), $a->user->nombre_completo ?? 'Sistema', $a->tienda->nombre ?? '—', $a->modulo, $a->accion,
                $a->auditable_type ? class_basename($a->auditable_type) . ' #' . $a->auditable_id : '—',
                $a->valores_nuevos ? mb_substr(json_encode($a->valores_nuevos, JSON_UNESCAPED_UNICODE), 0, 160) : '',
            ])->all();

        $cabeceras = ['Fecha', 'Usuario', 'Tienda', 'Módulo', 'Acción', 'Registro', 'Detalle'];
        $filtrosUi = [
            $this->fDate('desde', 'Desde', $desde), $this->fDate('hasta', 'Hasta', $hasta),
            $this->fSelect('modulo', 'Módulo', ['ventas' => 'Ventas', 'compras' => 'Compras', 'caja' => 'Caja', 'cxc' => 'Cuentas por cobrar', 'cxp' => 'Cuentas por pagar', 'transferencias' => 'Transferencias'], 'Todos'),
            ['name' => 'accion', 'label' => 'Acción contiene', 'type' => 'text', 'value' => $request->input('accion')],
        ];

        return $this->salida($request, 'Auditoría de operaciones', 'reportes.auditoria', $filtrosUi, $cabeceras, array_fill(0, 7, 'texto'), $filas, [],
            ['Mostrando' => 'hasta las 1000 últimas operaciones del período']);
    }

    public function accesos(Request $request)
    {
        [$desde, $hasta] = $this->fechas($request, 7);

        $filas = Acceso::with('user')
            ->whereDate('created_at', '>=', $desde)->whereDate('created_at', '<=', $hasta)
            ->when($request->filled('evento'), fn ($q) => $q->where('evento', $request->input('evento')))
            ->orderByDesc('created_at')->orderByDesc('id')->limit(1000)->get()
            ->map(fn ($a) => [$a->created_at->format('d/m/Y H:i:s'), $a->user->nombre_completo ?? '—', $a->email_intentado ?? '—', $a->evento, $a->ip ?? '—', mb_substr((string) $a->user_agent, 0, 80)])->all();

        $filtrosUi = [
            $this->fDate('desde', 'Desde', $desde), $this->fDate('hasta', 'Hasta', $hasta),
            $this->fSelect('evento', 'Evento', ['login' => 'Ingresos', 'logout' => 'Salidas', 'fallido' => 'Intentos fallidos'], 'Todos'),
        ];

        return $this->salida($request, 'Historial de accesos', 'reportes.accesos', $filtrosUi,
            ['Fecha', 'Usuario', 'Correo / usuario', 'Evento', 'IP', 'Navegador'], array_fill(0, 6, 'texto'), $filas, []);
    }

    /* ======================= Infraestructura ======================= */
    private function fechas(Request $request, ?int $diasAtras = null): array
    {
        $desde = $request->input('desde', $diasAtras ? today()->subDays($diasAtras)->toDateString() : today()->startOfMonth()->toDateString());
        $hasta = $request->input('hasta', today()->toDateString());

        return [$desde, $hasta];
    }

    private function fDate(string $name, string $label, $value): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'date', 'value' => $value];
    }

    private function fSelect(string $name, string $label, array $options, ?string $vacio = null, $value = null): array
    {
        return ['name' => $name, 'label' => $label, 'type' => 'select', 'options' => $options, 'vacio' => $vacio, 'value' => $value ?? request($name)];
    }

    private function formatear($valor, string $tipo, bool $csv): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        return match ($tipo) {
            'dinero' => $csv ? number_format((float) $valor, 2, ',', '') : Formato::dinero($valor),
            'cantidad' => $csv ? rtrim(rtrim(number_format((float) $valor, 3, ',', ''), '0'), ',') : Formato::cantidad($valor),
            'entero' => (string) (int) $valor,
            'fecha' => \Illuminate\Support\Carbon::parse($valor)->format('d/m/Y'),
            default => (string) $valor,
        };
    }

    private function salida(Request $request, string $titulo, string $ruta, array $filtros, array $cabeceras, array $tipos, array $filas, array $sumar = [], array $resumen = [])
    {
        $totales = null;
        if ($sumar && $filas) {
            $totales = array_fill(0, count($cabeceras), '');
            $totales[0] = 'TOTAL';
            foreach ($sumar as $i) {
                $totales[$i] = array_sum(array_map(fn ($f) => (float) $f[$i], $filas));
            }
        }

        $armar = function (bool $csv) use ($filas, $tipos, $totales) {
            $out = array_map(fn ($f) => array_map(fn ($v, $i) => $this->formatear($v, $tipos[$i], $csv), $f, array_keys($f)), $filas);
            $tot = $totales ? array_map(fn ($v, $i) => is_float($v) ? $this->formatear($v, $tipos[$i], $csv) : $v, $totales, array_keys($totales)) : null;

            return [$out, $tot];
        };

        if ($request->input('export') === 'csv') {
            [$out, $tot] = $armar(true);
            $nombre = str_replace('.', '_', $ruta) . '_' . now()->format('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($cabeceras, $out, $tot) {
                $h = fopen('php://output', 'w');
                fwrite($h, "\xEF\xBB\xBF"); // BOM para que Excel lea UTF-8
                fputcsv($h, $cabeceras, ';');
                foreach ($out as $f) {
                    fputcsv($h, $f, ';');
                }
                if ($tot) {
                    fputcsv($h, $tot, ';');
                }
                fclose($h);
            }, $nombre, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        [$out, $tot] = $armar(false);
        $alineacion = array_map(fn ($t) => in_array($t, ['dinero', 'cantidad', 'entero'], true) ? 'text-end' : '', $tipos);
        $breadcrumb = $this->breadcrumb(['Reportes' => null, $titulo => route($ruta)]);

        return view('ventas.reportes.tabla', [
            'titulo' => $titulo, 'ruta' => $ruta, 'filtros' => $filtros, 'cabeceras' => $cabeceras,
            'filas' => $out, 'totales' => $tot, 'alineacion' => $alineacion, 'resumen' => $resumen, 'breadcrumb' => $breadcrumb,
        ]);
    }
}
