<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\Transferencia;
use App\Services\TransferenciaService;
use App\Support\TiendaContext;
use Illuminate\Http\Request;

class TransferenciaController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private TransferenciaService $transferencias)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $ids = TiendaContext::ids();

        $transferencias = Transferencia::with(['tiendaOrigen', 'almacenOrigen', 'tiendaDestino', 'almacenDestino', 'usuarioEnvia'])
            ->where(fn ($t) => $t->whereIn('tienda_origen_id', $ids)->orWhereIn('tienda_destino_id', $ids))
            ->when($q !== '', fn ($t) => $t->where('numero', 'like', "%{$q}%"))
            ->when($request->filled('estado'), fn ($t) => $t->where('estado', $request->input('estado')))
            ->when($request->filled('tipo'), fn ($t) => $t->where('tipo', $request->input('tipo')))
            ->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(15)->withQueryString();

        $breadcrumb = $this->breadcrumb(['Transferencias' => route('transferencias.index')]);

        return view('ventas.transferencias.index', compact('transferencias', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $origenes = $this->almacenesDe(TiendaContext::ids());
        $destinos = Almacen::with('tienda')
            ->whereHas('tienda', fn ($t) => $t->where('empresa_id', $this->empresaId())->where('estado', 1))
            ->where('estado', 1)->orderBy('tienda_id')->orderBy('nombre')->get();

        $items = [];
        $breadcrumb = $this->breadcrumb(['Transferencias' => route('transferencias.index'), 'Nueva' => null]);

        return view('ventas.transferencias.create', [
            'opcionesOrigen' => $this->opcionesAlmacen($origenes),
            'opcionesDestino' => $this->opcionesAlmacen($destinos),
            'origenId' => $origenes->firstWhere('tienda_id', TiendaContext::actual()?->id)?->id ?? $origenes->first()?->id,
            'productos' => Producto::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')->get(),
            'stock' => $this->mapaStock($origenes->pluck('id')->all()),
            'items' => $items,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'almacen_origen_id' => ['required', 'exists:almacenes,id'],
            'almacen_destino_id' => ['required', 'exists:almacenes,id', 'different:almacen_origen_id'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0'],
        ], ['items.required' => 'Agrega al menos un producto.', 'almacen_destino_id.different' => 'El destino debe ser distinto al origen.']);

        try {
            $t = $this->transferencias->crear($data, auth()->user());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('transferencias.show', $t)->with('success', 'Transferencia creada. Envíala para descontar el stock del origen.');
    }

    public function show(Transferencia $transferencia)
    {
        $this->autorizar($transferencia);
        $transferencia->load(['tiendaOrigen', 'almacenOrigen', 'tiendaDestino', 'almacenDestino', 'usuarioEnvia', 'usuarioRecibe', 'detalles.producto', 'detalles.lote']);

        $ids = TiendaContext::ids();
        $puedeEnviar = in_array((int) $transferencia->tienda_origen_id, $ids, true);
        $puedeRecibir = in_array((int) $transferencia->tienda_destino_id, $ids, true);
        $breadcrumb = $this->breadcrumb(['Transferencias' => route('transferencias.index'), $transferencia->numero => null]);

        return view('ventas.transferencias.show', compact('transferencia', 'puedeEnviar', 'puedeRecibir', 'breadcrumb'));
    }

    public function enviar(Transferencia $transferencia)
    {
        return $this->ejecutar($transferencia, 'enviar', 'Transferencia enviada: el stock salió del origen.');
    }

    public function recibir(Transferencia $transferencia)
    {
        return $this->ejecutar($transferencia, 'recibir', 'Transferencia recibida: el stock ingresó al destino.');
    }

    public function anular(Transferencia $transferencia)
    {
        return $this->ejecutar($transferencia, 'anular', 'Transferencia anulada.');
    }

    private function ejecutar(Transferencia $transferencia, string $accion, string $mensaje)
    {
        $this->autorizar($transferencia);

        try {
            $this->transferencias->{$accion}($transferencia, auth()->user());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('transferencias.show', $transferencia)->with('success', $mensaje);
    }

    private function autorizar(Transferencia $transferencia): void
    {
        $ids = TiendaContext::ids();
        abort_unless(in_array((int) $transferencia->tienda_origen_id, $ids, true) || in_array((int) $transferencia->tienda_destino_id, $ids, true), 403);
    }
}
