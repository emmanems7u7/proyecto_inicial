<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\CajaMovimiento;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use App\Services\CajaService;
use App\Services\VentaService;
use App\Support\Catalogos;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VentaController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private VentaService $ventas, private CajaService $caja)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $q = trim((string) $request->input('q'));
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');

        $base = Venta::query()
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when(! $user->can('ventas.ver_todas'), fn ($v) => $v->where('vendedor_id', $user->id))
            ->when($q !== '', fn ($v) => $v->where(function ($w) use ($q) {
                $w->where('numero', 'like', "%{$q}%")->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$q}%"));
            }))
            ->when($request->filled('tienda_id'), fn ($v) => $v->where('tienda_id', $request->input('tienda_id')))
            ->when($request->filled('estado'), fn ($v) => $v->where('estado', $request->input('estado')))
            ->when($request->filled('tipo_pago'), fn ($v) => $v->where('tipo_pago', $request->input('tipo_pago')))
            ->when($request->filled('vendedor_id') && $user->can('ventas.ver_todas'), fn ($v) => $v->where('vendedor_id', $request->input('vendedor_id')))
            ->when($request->filled('desde'), fn ($v) => $v->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($v) => $v->whereDate('fecha', '<=', $request->input('hasta')));

        $totalFiltrado = (float) (clone $base)->where('estado', 'completada')->sum('total');
        $ventas = (clone $base)->with(['cliente', 'vendedor', 'tienda'])
            ->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        $vendedores = $user->can('ventas.ver_todas')
            ? User::where('empresa_id', $this->empresaId())->orderBy('usuario_nombres')->get()->pluck('nombre_completo', 'id')
            : collect();

        $breadcrumb = $this->breadcrumb(['Ventas' => route('ventas.index')]);

        return view('ventas.ventas.index', compact('ventas', 'tiendas', 'vendedores', 'totalFiltrado', 'q', 'breadcrumb'));
    }

    public function create(Request $request)
    {
        $tienda = $this->tiendaActual();
        $almacenes = $this->almacenesDe([$tienda->id]);
        $items = [];
        $cotizacion = null;
        $descuento = 0;

        if ($request->filled('cotizacion_id')) {
            $cotizacion = Cotizacion::with('detalles')->where('tienda_id', $tienda->id)->findOrFail($request->input('cotizacion_id'));
            abort_if($cotizacion->estado === 'convertida', 403, 'La cotización ya se convirtió en venta.');
            $descuento = (float) $cotizacion->descuento;
            $items = $cotizacion->detalles->map(fn ($d) => [
                'producto_id' => $d->producto_id, 'cantidad' => (float) $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario, 'descuento' => (float) $d->descuento,
            ])->all();
        }

        $datos = [
            'tienda' => $tienda,
            'opcionesAlmacen' => $almacenes->pluck('nombre', 'id'),
            'almacenId' => $almacenes->firstWhere('es_principal', true)?->id ?? $almacenes->first()?->id,
            'productos' => Producto::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->get(),
            'stock' => $this->mapaStock($almacenes->pluck('id')->all()),
            'clientes' => Cliente::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->pluck('nombre', 'id'),
            'metodos' => Catalogos::metodosPago(),
            'efectivoId' => $this->caja->metodoEfectivoId(),
            'sesion' => $this->caja->sesionAbiertaDeUsuario(auth()->id(), $tienda->id),
            'clienteId' => $cotizacion?->cliente_id,
        ];
        $breadcrumb = $this->breadcrumb(['Ventas' => route('ventas.index'), 'Nueva venta' => null]);

        return view('ventas.ventas.create', $datos + compact('items', 'cotizacion', 'descuento', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        $tienda = $this->tiendaActual();

        $data = $request->validate([
            'almacen_id' => ['required', Rule::exists('almacenes', 'id')->where('tienda_id', $tienda->id)],
            'cliente_id' => ['nullable', Rule::exists('clientes', 'id')->where('empresa_id', $this->empresaId())],
            'tipo_pago' => ['required', 'in:contado,credito'],
            'metodo_pago_id' => ['nullable', 'exists:catalogos,id'],
            'anticipo' => ['nullable', 'numeric', 'min:0'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'cotizacion_id' => ['nullable', 'integer'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ], ['items.required' => 'Agrega al menos un producto.']);
        $data['como_pedido'] = $request->boolean('como_pedido');

        try {
            $venta = $this->ventas->registrar($tienda, $data, auth()->user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('ventas.show', $venta)->with('success', $venta->estado === 'pedido' ? 'Pedido guardado.' : 'Venta registrada.');
    }

    public function show(Venta $venta)
    {
        $this->autorizarVenta($venta);
        $venta->load(['tienda', 'almacen', 'cliente', 'vendedor', 'detalles.producto', 'detalles.lote', 'cuentaPorCobrar.pagos.metodoPago', 'devoluciones.user']);

        $movimientos = CajaMovimiento::with('metodoPago')
            ->where('referencia_type', $venta->getMorphClass())->where('referencia_id', $venta->id)->get();
        $pendientes = $venta->estado === 'completada' ? $this->ventas->pendientesDevolucion($venta) : collect();
        $metodos = Catalogos::metodosPago();
        $efectivoId = $this->caja->metodoEfectivoId();
        $breadcrumb = $this->breadcrumb(['Ventas' => route('ventas.index'), $venta->numero => null]);

        return view('ventas.ventas.show', compact('venta', 'movimientos', 'pendientes', 'metodos', 'efectivoId', 'breadcrumb'));
    }

    public function imprimir(Venta $venta)
    {
        $this->autorizarVenta($venta);
        $venta->load(['tienda.empresa', 'almacen', 'cliente', 'vendedor', 'detalles.producto.unidad', 'detalles.lote']);

        return view('ventas.ventas.imprimir', compact('venta'));
    }

    public function confirmar(Request $request, Venta $venta)
    {
        $this->autorizarVenta($venta);

        $data = $request->validate([
            'tipo_pago' => ['required', 'in:contado,credito'],
            'metodo_pago_id' => ['nullable', 'exists:catalogos,id'],
            'anticipo' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $this->ventas->confirmarPedido($venta, $data, auth()->user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('ventas.show', $venta)->with('success', 'Pedido confirmado como venta.');
    }

    public function anular(Request $request, Venta $venta)
    {
        $this->autorizarVenta($venta);
        $motivo = $request->validate(['motivo' => ['required', 'string', 'max:255']])['motivo'];

        try {
            $this->ventas->anular($venta, $motivo, auth()->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('ventas.show', $venta)->with('success', 'Venta anulada y stock restituido.');
    }

    public function devolucionCreate(Venta $venta)
    {
        $this->autorizarVenta($venta);
        abort_unless($venta->estado === 'completada', 403, 'Solo se pueden devolver productos de ventas completadas.');

        $venta->load(['tienda', 'cliente']);
        $pendientes = $this->ventas->pendientesDevolucion($venta);
        $breadcrumb = $this->breadcrumb(['Ventas' => route('ventas.index'), $venta->numero => route('ventas.show', $venta), 'Devolución' => null]);

        return view('ventas.ventas.devolucion', compact('venta', 'pendientes', 'breadcrumb'));
    }

    public function devolucionStore(Request $request, Venta $venta)
    {
        $this->autorizarVenta($venta);

        $data = $request->validate([
            'cantidades' => ['required', 'array'],
            'cantidades.*' => ['nullable', 'numeric', 'min:0'],
            'motivo' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $dev = $this->ventas->devolver($venta, $data['cantidades'], $data['motivo'] ?? null, auth()->user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('ventas.show', $venta)->with('success', "Devolución {$dev->numero} registrada.");
    }

    private function autorizarVenta(Venta $venta): void
    {
        abort_unless(in_array((int) $venta->tienda_id, TiendaContext::ids(), true), 403);
        abort_unless(auth()->user()->can('ventas.ver_todas') || (int) $venta->vendedor_id === (int) auth()->id(), 403);
    }
}
