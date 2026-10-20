<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Exceptions\InventarioException;
use App\Models\Almacen;
use App\Models\Inventario;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventarioService;
use App\Support\Catalogos;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventarioController extends Controller
{
    use UsaContexto;

    public function __construct(private InventarioService $inventario)
    {
    }

    /* ---------- Stock por almacén ---------- */
    public function index(Request $request)
    {
        $almacenes = $this->almacenesPermitidos();
        $almacen = $almacenes->firstWhere('id', (int) $request->input('almacen_id')) ?? $this->almacenPorDefecto($almacenes);
        $almacenId = $almacen?->id ?? 0;

        $q = trim((string) $request->input('q'));
        $estadoStock = $request->input('estado_stock');

        $productos = Producto::where('empresa_id', $this->empresaId())
            ->where('estado', 1)
            ->with(['unidad', 'inventarios' => fn ($i) => $i->where('almacen_id', $almacenId)])
            ->buscar($q)
            ->when($estadoStock === 'con_stock', fn ($p) => $p->whereHas('inventarios', fn ($i) => $i->where('almacen_id', $almacenId)->where('cantidad', '>', 0)))
            ->when($estadoStock === 'sin_stock', fn ($p) => $p->whereDoesntHave('inventarios', fn ($i) => $i->where('almacen_id', $almacenId)->where('cantidad', '>', 0)))
            ->when($estadoStock === 'bajo_minimo', fn ($p) => $p->whereHas('inventarios', fn ($i) => $i->where('almacen_id', $almacenId)->where('stock_minimo', '>', 0)->whereColumn('cantidad', '<=', 'stock_minimo')))
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $opcionesAlmacen = $this->opcionesAlmacen($almacenes);
        $breadcrumb = $this->breadcrumb(['Inventario' => route('inventario.index')]);

        return view('ventas.inventario.index', compact('productos', 'almacen', 'almacenId', 'opcionesAlmacen', 'q', 'estadoStock', 'breadcrumb'));
    }

    /* ---------- Registrar movimiento manual ---------- */
    public function movimientoCreate(Request $request)
    {
        $almacenes = $this->almacenesPermitidos();
        $opcionesAlmacen = $this->opcionesAlmacen($almacenes);
        $productos = Producto::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('nombre')
            ->get(['id', 'codigo', 'nombre', 'maneja_lotes', 'maneja_vencimiento']);
        $motivos = Catalogos::motivosAjuste();

        $almacenId = $request->input('almacen_id') ?: $this->almacenPorDefecto($almacenes)?->id;
        $productoId = $request->input('producto_id');

        $breadcrumb = $this->breadcrumb(['Inventario' => route('inventario.index'), 'Registrar movimiento' => null]);

        return view('ventas.inventario.movimiento', compact('opcionesAlmacen', 'productos', 'motivos', 'almacenId', 'productoId', 'breadcrumb'));
    }

    public function movimientoStore(Request $request)
    {
        $data = $request->validate([
            'almacen_id' => ['required', 'exists:almacenes,id'],
            'producto_id' => ['required', Rule::exists('productos', 'id')->where('empresa_id', $this->empresaId())],
            'operacion' => ['required', 'in:entrada,salida,ajuste_mas,ajuste_menos'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'motivo_id' => ['nullable', 'required_unless:operacion,entrada', 'exists:catalogos,id'],
            'numero_lote' => ['nullable', 'string', 'max:60'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        $almacen = Almacen::findOrFail($data['almacen_id']);
        $this->autorizarTienda($almacen->tienda_id);

        [$tipo, $signo] = match ($data['operacion']) {
            'entrada' => ['entrada', 1],
            'salida' => ['salida', -1],
            'ajuste_mas' => ['ajuste', 1],
            'ajuste_menos' => ['ajuste', -1],
        };

        $opts = [
            'motivo_id' => $data['motivo_id'] ?? null,
            'numero_lote' => $data['numero_lote'] ?? null,
            'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
            'costo_unitario' => $data['costo_unitario'] ?? null,
            'observacion' => $data['observacion'] ?? null,
            'incluir_vencidos' => true, // manual: permite dar de baja lotes vencidos
        ];
        if ($opts['costo_unitario'] === null) {
            unset($opts['costo_unitario']);
        }

        try {
            $this->inventario->registrar($tipo, $signo, $almacen->id, (int) $data['producto_id'], (float) $data['cantidad'], $opts);
        } catch (InventarioException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('inventario.kardex', ['almacen_id' => $almacen->id, 'producto_id' => $data['producto_id']])
            ->with('success', 'Movimiento registrado.');
    }

    /* ---------- Kardex ---------- */
    public function kardex(Request $request)
    {
        $almacenes = $this->almacenesPermitidos();
        $q = trim((string) $request->input('q'));
        $producto = $request->filled('producto_id') ? Producto::where('empresa_id', $this->empresaId())->find($request->input('producto_id')) : null;

        $movimientos = MovimientoInventario::with(['almacen.tienda', 'producto', 'lote', 'user', 'motivo'])
            ->whereIn('almacen_id', $almacenes->pluck('id'))
            ->when($request->filled('almacen_id'), fn ($m) => $m->where('almacen_id', $request->input('almacen_id')))
            ->when($producto, fn ($m) => $m->where('producto_id', $producto->id))
            ->when($q !== '', fn ($m) => $m->whereHas('producto', fn ($p) => $p->buscar($q)))
            ->when($request->filled('tipo'), fn ($m) => $m->where('tipo', $request->input('tipo')))
            ->when($request->filled('desde'), fn ($m) => $m->whereDate('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($m) => $m->whereDate('fecha', '<=', $request->input('hasta')))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $opcionesAlmacen = $this->opcionesAlmacen($almacenes);
        $tipos = MovimientoInventario::TIPOS;
        $breadcrumb = $this->breadcrumb(['Inventario' => route('inventario.index'), 'Kardex' => null]);

        return view('ventas.inventario.kardex', compact('movimientos', 'producto', 'opcionesAlmacen', 'tipos', 'q', 'breadcrumb'));
    }

    /* ---------- Lotes y vencimientos ---------- */
    public function lotes(Request $request)
    {
        $almacenes = $this->almacenesPermitidos();
        $q = trim((string) $request->input('q'));

        $lotes = Lote::with(['producto', 'almacen.tienda'])
            ->whereIn('almacen_id', $almacenes->pluck('id'))
            ->whereHas('producto', fn ($p) => $p->where('empresa_id', $this->empresaId()))
            ->when($request->filled('almacen_id'), fn ($l) => $l->where('almacen_id', $request->input('almacen_id')))
            ->when($q !== '', fn ($l) => $l->where(function ($w) use ($q) {
                $w->where('numero_lote', 'like', "%{$q}%")->orWhereHas('producto', fn ($p) => $p->buscar($q));
            }))
            ->estadoVencimiento($request->input('estado'))
            ->orderByRaw('fecha_vencimiento IS NULL')
            ->orderBy('fecha_vencimiento')
            ->paginate(25)
            ->withQueryString();

        $opcionesAlmacen = $this->opcionesAlmacen($almacenes);
        $breadcrumb = $this->breadcrumb(['Inventario' => route('inventario.index'), 'Lotes y vencimientos' => null]);

        return view('ventas.inventario.lotes', compact('lotes', 'opcionesAlmacen', 'q', 'breadcrumb'));
    }

    /* ---------- Configurar stock mínimo / máximo ---------- */
    public function configEdit(Request $request)
    {
        $request->validate([
            'almacen_id' => ['required', 'exists:almacenes,id'],
            'producto_id' => ['required', 'exists:productos,id'],
        ]);

        $almacen = Almacen::findOrFail($request->input('almacen_id'));
        $this->autorizarTienda($almacen->tienda_id);
        $producto = Producto::findOrFail($request->input('producto_id'));
        $this->autorizarEmpresa($producto);

        $inventario = Inventario::firstOrCreate(
            ['almacen_id' => $almacen->id, 'producto_id' => $producto->id],
            ['cantidad' => 0, 'stock_minimo' => 0]
        );
        $inventario->setRelation('almacen', $almacen)->setRelation('producto', $producto);

        $breadcrumb = $this->breadcrumb(['Inventario' => route('inventario.index'), 'Configurar stock' => null]);

        return view('ventas.inventario.config', compact('inventario', 'breadcrumb'));
    }

    public function configUpdate(Request $request, Inventario $inventario)
    {
        $this->autorizarTienda($inventario->almacen->tienda_id);

        $data = $request->validate([
            'stock_minimo' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock_maximo' => ['nullable', 'numeric', 'gte:stock_minimo', 'max:99999999'],
            'ubicacion' => ['nullable', 'string', 'max:60'],
        ]);

        $inventario->fill($data);
        if ($inventario->cantidad > $inventario->stock_minimo) {
            $inventario->alerta_stock_enviada_at = null;
        }
        $inventario->save();

        return redirect()->route('inventario.index', ['almacen_id' => $inventario->almacen_id])
            ->with('success', 'Configuración de stock actualizada.');
    }

    /* ---------- Ayudas ---------- */
    private function almacenesPermitidos()
    {
        return Almacen::with('tienda')
            ->whereIn('tienda_id', TiendaContext::ids())
            ->where('estado', 1)
            ->orderBy('tienda_id')
            ->orderByDesc('es_principal')
            ->orderBy('nombre')
            ->get();
    }

    private function opcionesAlmacen($almacenes)
    {
        return $almacenes->mapWithKeys(fn ($a) => [$a->id => $a->nombre_completo]);
    }

    /** Almacén principal de la tienda activa, o el primero disponible. */
    private function almacenPorDefecto($almacenes): ?Almacen
    {
        $tiendaId = TiendaContext::actual()?->id;

        return $almacenes->firstWhere('tienda_id', $tiendaId) ?? $almacenes->first();
    }
}
