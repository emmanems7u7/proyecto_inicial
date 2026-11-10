<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Exceptions\OperacionException;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CotizacionDetalle;
use App\Models\Producto;
use App\Services\SecuenciaService;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotizacionController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private SecuenciaService $secuencias)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $cotizaciones = Cotizacion::with(['cliente', 'vendedor', 'tienda'])
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($q !== '', fn ($query) => $query->where(function ($w) use ($q) {
                $w->where('numero', 'like', "%{$q}%")->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$q}%"));
            }))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->input('estado')))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('fecha', '<=', $request->input('hasta')))
            ->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        $breadcrumb = $this->breadcrumb(['Cotizaciones' => route('cotizaciones.index')]);

        return view('ventas.cotizaciones.index', compact('cotizaciones', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $cotizacion = new Cotizacion(['fecha_validez' => today()->addDays(7), 'descuento' => 0]);
        $items = [];
        $breadcrumb = $this->breadcrumb(['Cotizaciones' => route('cotizaciones.index'), 'Nueva' => null]);

        return view('ventas.cotizaciones.create', array_merge($this->datosFormulario(), compact('cotizacion', 'items', 'breadcrumb')));
    }

    public function store(Request $request)
    {
        $tienda = $this->tiendaActual();

        try {
            [$data, $lineas] = $this->validar($request);
            $cotizacion = DB::transaction(function () use ($tienda, $data, $lineas) {
                $c = Cotizacion::create($data + [
                    'tienda_id' => $tienda->id,
                    'user_id' => auth()->id(),
                    'numero' => $this->secuencias->siguiente($tienda->id, 'cotizacion', 'COT'),
                    'fecha' => today()->toDateString(),
                    'estado' => 'pendiente',
                ]);
                $this->guardarDetalles($c, $lineas);

                return $c;
            });
        } catch (OperacionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cotizaciones.show', $cotizacion)->with('success', 'Cotización creada.');
    }

    public function show(Cotizacion $cotizacion)
    {
        $this->autorizar($cotizacion);
        $cotizacion->load(['cliente', 'vendedor', 'tienda', 'detalles.producto']);
        $breadcrumb = $this->breadcrumb(['Cotizaciones' => route('cotizaciones.index'), $cotizacion->numero => null]);

        return view('ventas.cotizaciones.show', compact('cotizacion', 'breadcrumb'));
    }

    public function imprimir(Cotizacion $cotizacion)
    {
        $this->autorizar($cotizacion);
        $cotizacion->load(['cliente', 'vendedor', 'tienda.empresa', 'detalles.producto.unidad']);

        return view('ventas.cotizaciones.imprimir', compact('cotizacion'));
    }

    public function edit(Cotizacion $cotizacion)
    {
        $this->autorizar($cotizacion);
        abort_unless($cotizacion->estado === 'pendiente', 403, 'Solo se pueden editar cotizaciones pendientes.');

        $items = $cotizacion->detalles->map(fn ($d) => [
            'producto_id' => $d->producto_id, 'cantidad' => (float) $d->cantidad,
            'precio_unitario' => (float) $d->precio_unitario, 'descuento' => (float) $d->descuento,
        ])->all();
        $breadcrumb = $this->breadcrumb(['Cotizaciones' => route('cotizaciones.index'), 'Editar' => null]);

        return view('ventas.cotizaciones.edit', array_merge($this->datosFormulario(), compact('cotizacion', 'items', 'breadcrumb')));
    }

    public function update(Request $request, Cotizacion $cotizacion)
    {
        $this->autorizar($cotizacion);
        abort_unless($cotizacion->estado === 'pendiente', 403);

        try {
            [$data, $lineas] = $this->validar($request);
            DB::transaction(function () use ($cotizacion, $data, $lineas) {
                $cotizacion->update($data);
                $cotizacion->detalles()->delete();
                $this->guardarDetalles($cotizacion, $lineas);
            });
        } catch (OperacionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cotizaciones.show', $cotizacion)->with('success', 'Cotización actualizada.');
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $this->autorizar($cotizacion);

        if ($cotizacion->estado === 'convertida') {
            return back()->with('error', 'La cotización ya se convirtió en venta.');
        }

        $cotizacion->delete();

        return redirect()->route('cotizaciones.index')->with('success', 'Cotización eliminada.');
    }

    public function cambiarEstado(Request $request, Cotizacion $cotizacion)
    {
        $this->autorizar($cotizacion);
        $estado = $request->validate(['estado' => ['required', 'in:aceptada,rechazada,pendiente']])['estado'];

        if ($cotizacion->estado === 'convertida') {
            return back()->with('error', 'La cotización ya se convirtió en venta.');
        }

        $cotizacion->update(['estado' => $estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    /* ---------------- internos ---------------- */
    private function autorizar(Cotizacion $cotizacion): void
    {
        $this->autorizarTienda($cotizacion->tienda_id);
    }

    private function datosFormulario(): array
    {
        $empresaId = $this->empresaId();

        return [
            'clientes' => Cliente::where('empresa_id', $empresaId)->where('estado', 1)->orderBy('nombre')->pluck('nombre', 'id'),
            'productos' => Producto::where('empresa_id', $empresaId)->where('estado', 1)->orderBy('nombre')->get(),
        ];
    }

    /** @return array{0: array, 1: array} [datos de cabecera, líneas] */
    private function validar(Request $request): array
    {
        $r = $request->validate([
            'cliente_id' => ['nullable', 'exists:clientes,id'],
            'fecha_validez' => ['nullable', 'date'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'items.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ], ['items.required' => 'Agrega al menos un producto.']);

        $productos = Producto::where('empresa_id', $this->empresaId())->whereIn('id', collect($r['items'])->pluck('producto_id'))->pluck('id');
        $lineas = [];
        foreach ($r['items'] as $i) {
            if (! $productos->contains((int) $i['producto_id'])) {
                throw new OperacionException('Hay un producto inválido en el detalle.');
            }
            $cant = round((float) $i['cantidad'], 3);
            $precio = round((float) $i['precio_unitario'], 2);
            $desc = round((float) ($i['descuento'] ?? 0), 2);
            $bruto = round($cant * $precio, 2);
            if ($desc > $bruto) {
                throw new OperacionException('Un descuento de línea supera su importe.');
            }
            $lineas[] = ['producto_id' => (int) $i['producto_id'], 'cantidad' => $cant, 'precio_unitario' => $precio, 'descuento' => $desc, 'subtotal' => round($bruto - $desc, 2)];
        }

        $subtotal = round(array_sum(array_column($lineas, 'subtotal')), 2);
        $descuento = round((float) ($r['descuento'] ?? 0), 2);
        if ($descuento > $subtotal) {
            throw new OperacionException('El descuento no puede superar el subtotal.');
        }

        return [[
            'cliente_id' => $r['cliente_id'] ?? null,
            'fecha_validez' => $r['fecha_validez'] ?? null,
            'observaciones' => $r['observaciones'] ?? null,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'total' => round($subtotal - $descuento, 2),
        ], $lineas];
    }

    private function guardarDetalles(Cotizacion $cotizacion, array $lineas): void
    {
        foreach ($lineas as $l) {
            CotizacionDetalle::create($l + ['cotizacion_id' => $cotizacion->id]);
        }
    }
}
