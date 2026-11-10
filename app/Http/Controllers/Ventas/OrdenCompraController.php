<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Exceptions\OperacionException;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraDetalle;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\SecuenciaService;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrdenCompraController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private SecuenciaService $secuencias)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $ordenes = OrdenCompra::with(['proveedor', 'tienda', 'user'])
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('numero', 'like', "%{$q}%")->orWhereHas('proveedor', fn ($p) => $p->where('nombre', 'like', "%{$q}%"));
            }))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->input('estado')))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('fecha', '<=', $request->input('hasta')))
            ->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        $breadcrumb = $this->breadcrumb(['Órdenes de compra' => route('ordenes_compra.index')]);

        return view('ventas.ordenes_compra.index', compact('ordenes', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $tienda = $this->tiendaActual();
        $almacenes = $this->almacenesDe([$tienda->id]);
        $items = [];
        $breadcrumb = $this->breadcrumb(['Órdenes de compra' => route('ordenes_compra.index'), 'Nueva' => null]);

        return view('ventas.ordenes_compra.create', [
            'tienda' => $tienda,
            'opcionesAlmacen' => $almacenes->pluck('nombre', 'id'),
            'almacenId' => $almacenes->firstWhere('es_principal', true)?->id ?? $almacenes->first()?->id,
            'proveedores' => Proveedor::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->pluck('nombre', 'id'),
            'productos' => Producto::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->get(),
            'items' => $items,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function store(Request $request)
    {
        $tienda = $this->tiendaActual();
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'almacen_id' => ['required', Rule::exists('almacenes', 'id')->where('tienda_id', $tienda->id)],
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('empresa_id', $empresaId)],
            'fecha_entrega_estimada' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.costo_unitario' => ['required', 'numeric', 'min:0'],
        ], ['items.required' => 'Agrega al menos un producto.']);

        $validos = Producto::where('empresa_id', $empresaId)->whereIn('id', collect($data['items'])->pluck('producto_id'))->pluck('id');

        try {
            $orden = DB::transaction(function () use ($tienda, $data, $validos) {
                $lineas = [];
                foreach ($data['items'] as $i) {
                    if (! $validos->contains((int) $i['producto_id'])) {
                        throw new OperacionException('Hay un producto inválido en el detalle.');
                    }
                    $cant = round((float) $i['cantidad'], 3);
                    $costo = round((float) $i['costo_unitario'], 2);
                    $lineas[] = ['producto_id' => (int) $i['producto_id'], 'cantidad' => $cant, 'costo_unitario' => $costo, 'subtotal' => round($cant * $costo, 2)];
                }
                $total = round(array_sum(array_column($lineas, 'subtotal')), 2);

                $orden = OrdenCompra::create([
                    'tienda_id' => $tienda->id,
                    'almacen_id' => $data['almacen_id'],
                    'proveedor_id' => $data['proveedor_id'],
                    'user_id' => auth()->id(),
                    'numero' => $this->secuencias->siguiente($tienda->id, 'orden_compra', 'OC'),
                    'fecha' => today()->toDateString(),
                    'fecha_entrega_estimada' => $data['fecha_entrega_estimada'] ?? null,
                    'subtotal' => $total,
                    'total' => $total,
                    'estado' => 'pendiente',
                    'observaciones' => $data['observaciones'] ?? null,
                ]);
                foreach ($lineas as $l) {
                    OrdenCompraDetalle::create($l + ['orden_compra_id' => $orden->id, 'cantidad_recibida' => 0]);
                }

                return $orden;
            });
        } catch (OperacionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('ordenes_compra.show', $orden)->with('success', 'Orden de compra creada.');
    }

    public function show(OrdenCompra $orden)
    {
        $this->autorizarTienda($orden->tienda_id);
        $orden->load(['proveedor', 'tienda', 'almacen', 'user', 'detalles.producto']);
        $breadcrumb = $this->breadcrumb(['Órdenes de compra' => route('ordenes_compra.index'), $orden->numero => null]);

        return view('ventas.ordenes_compra.show', compact('orden', 'breadcrumb'));
    }

    public function anular(OrdenCompra $orden)
    {
        $this->autorizarTienda($orden->tienda_id);

        if ($orden->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden anular órdenes pendientes (sin recepciones).');
        }

        $orden->update(['estado' => 'anulada']);

        return redirect()->route('ordenes_compra.show', $orden)->with('success', 'Orden anulada.');
    }
}
