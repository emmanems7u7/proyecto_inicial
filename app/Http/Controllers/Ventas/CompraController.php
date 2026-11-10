<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\CajaMovimiento;
use App\Models\Compra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\CajaService;
use App\Services\CompraService;
use App\Support\Catalogos;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompraController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private CompraService $compras, private CajaService $caja)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $base = Compra::query()
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($q !== '', fn ($c) => $c->where(function ($w) use ($q) {
                $w->where('numero', 'like', "%{$q}%")->orWhere('numero_documento_proveedor', 'like', "%{$q}%")
                    ->orWhereHas('proveedor', fn ($p) => $p->where('nombre', 'like', "%{$q}%"));
            }))
            ->when($request->filled('estado'), fn ($c) => $c->where('estado', $request->input('estado')))
            ->when($request->filled('tipo_pago'), fn ($c) => $c->where('tipo_pago', $request->input('tipo_pago')))
            ->when($request->filled('desde'), fn ($c) => $c->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($c) => $c->whereDate('fecha', '<=', $request->input('hasta')));

        $totalFiltrado = (float) (clone $base)->where('estado', 'recibida')->sum('total');
        $compras = (clone $base)->with(['proveedor', 'tienda'])->orderByDesc('fecha')->orderByDesc('id')->paginate(15)->withQueryString();
        $breadcrumb = $this->breadcrumb(['Compras' => route('compras.index')]);

        return view('ventas.compras.index', compact('compras', 'totalFiltrado', 'q', 'breadcrumb'));
    }

    public function create(Request $request)
    {
        $tienda = $this->tiendaActual();
        $almacenes = $this->almacenesDe([$tienda->id]);
        $items = [];
        $orden = null;

        if ($request->filled('orden_compra_id')) {
            $orden = OrdenCompra::with('detalles')->where('tienda_id', $tienda->id)->findOrFail($request->input('orden_compra_id'));
            abort_if(in_array($orden->estado, ['recibida', 'anulada']), 403, 'La orden de compra no admite recepciones.');
            foreach ($orden->detalles as $d) {
                $pendiente = round($d->cantidad - $d->cantidad_recibida, 3);
                if ($pendiente > 0) {
                    $items[] = ['producto_id' => $d->producto_id, 'cantidad' => $pendiente, 'costo_unitario' => (float) $d->costo_unitario, 'descuento' => 0];
                }
            }
        }

        $breadcrumb = $this->breadcrumb(['Compras' => route('compras.index'), 'Nueva compra' => null]);

        return view('ventas.compras.create', [
            'tienda' => $tienda,
            'orden' => $orden,
            'opcionesAlmacen' => $almacenes->pluck('nombre', 'id'),
            'almacenId' => $orden?->almacen_id ?? $almacenes->firstWhere('es_principal', true)?->id ?? $almacenes->first()?->id,
            'proveedores' => Proveedor::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->pluck('nombre', 'id'),
            'proveedorId' => $orden?->proveedor_id,
            'productos' => Producto::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->get(),
            'metodos' => Catalogos::metodosPago(),
            'efectivoId' => $this->caja->metodoEfectivoId(),
            'sesion' => $this->caja->sesionAbiertaDeUsuario(auth()->id(), $tienda->id),
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
            'numero_documento_proveedor' => ['nullable', 'string', 'max:40'],
            'tipo_pago' => ['required', 'in:contado,credito'],
            'metodo_pago_id' => ['nullable', 'exists:catalogos,id'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'orden_compra_id' => ['nullable', 'integer'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.costo_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
            'items.*.numero_lote' => ['nullable', 'string', 'max:60'],
            'items.*.fecha_vencimiento' => ['nullable', 'date'],
        ], ['items.required' => 'Agrega al menos un producto.']);
        $data['pagar_caja'] = $request->boolean('pagar_caja');

        try {
            $compra = $this->compras->registrar($tienda, $data, auth()->user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('compras.show', $compra)->with('success', 'Compra registrada y stock actualizado.');
    }

    public function show(Compra $compra)
    {
        $this->autorizarTienda($compra->tienda_id);
        $compra->load(['tienda', 'almacen', 'proveedor', 'user', 'ordenCompra', 'detalles.producto', 'cuentaPorPagar.pagos.metodoPago', 'devoluciones.user']);
        $breadcrumb = $this->breadcrumb(['Compras' => route('compras.index'), $compra->numero => null]);

        return view('ventas.compras.show', compact('compra', 'breadcrumb'));
    }

    public function anular(Compra $compra)
    {
        $this->autorizarTienda($compra->tienda_id);

        try {
            $this->compras->anular($compra, auth()->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('compras.show', $compra)->with('success', 'Compra anulada y stock revertido.');
    }

    public function devolucionCreate(Compra $compra)
    {
        $this->autorizarTienda($compra->tienda_id);
        abort_unless($compra->estado === 'recibida', 403, 'Solo se pueden devolver productos de compras recibidas.');

        $compra->load(['tienda', 'proveedor']);
        $pendientes = $this->compras->pendientesDevolucion($compra);
        $breadcrumb = $this->breadcrumb(['Compras' => route('compras.index'), $compra->numero => route('compras.show', $compra), 'Devolución' => null]);

        return view('ventas.compras.devolucion', compact('compra', 'pendientes', 'breadcrumb'));
    }

    public function devolucionStore(Request $request, Compra $compra)
    {
        $this->autorizarTienda($compra->tienda_id);

        $data = $request->validate([
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'numeric', 'min:0'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $dev = $this->compras->devolver($compra, $data['cantidades'], $data['motivo'] ?? null, auth()->user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('compras.show', $compra)->with('success', "Devolución {$dev->numero} registrada.");
    }
}
