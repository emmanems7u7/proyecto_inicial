<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Support\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $productos = Producto::with(['categoria', 'unidad'])
            ->withSum('inventarios as stock_total', 'cantidad')
            ->where('empresa_id', $this->empresaId())
            ->buscar($q)
            ->when($request->filled('categoria_producto_id'), fn ($query) => $query->where('categoria_producto_id', $request->input('categoria_producto_id')))
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', (int) $request->input('estado')))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $categorias = $this->categorias();
        $breadcrumb = $this->breadcrumb(['Productos' => route('productos.index')]);

        return view('ventas.productos.index', compact('productos', 'categorias', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $producto = new Producto(['estado' => 1, 'precio_compra' => 0, 'precio_venta' => 0]);
        $categorias = $this->categorias();
        $unidades = Catalogos::unidades();
        $breadcrumb = $this->breadcrumb(['Productos' => route('productos.index'), 'Nuevo' => null]);

        return view('ventas.productos.create', compact('producto', 'categorias', 'unidades', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        Producto::create($this->validar($request) + ['empresa_id' => $this->empresaId()]);

        return redirect()->route('productos.index')->with('success', 'Producto creado. Registra su stock inicial en Inventario > Registrar movimiento.');
    }

    public function edit(Producto $producto)
    {
        $this->autorizarEmpresa($producto);
        $categorias = $this->categorias();
        $unidades = Catalogos::unidades();
        $breadcrumb = $this->breadcrumb(['Productos' => route('productos.index'), 'Editar' => null]);

        return view('ventas.productos.edit', compact('producto', 'categorias', 'unidades', 'breadcrumb'));
    }

    public function update(Request $request, Producto $producto)
    {
        $this->autorizarEmpresa($producto);
        $data = $this->validar($request, $producto);

        // No se puede cambiar el manejo de lotes si ya hay stock: los saldos por lote quedarían inconsistentes.
        if ((bool) $producto->maneja_lotes !== (bool) $data['maneja_lotes']
            && $producto->inventarios()->where('cantidad', '>', 0)->exists()) {
            return back()->withInput()->with('error', 'No puedes cambiar el manejo de lotes mientras el producto tenga stock.');
        }

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        $this->autorizarEmpresa($producto);

        if ($producto->movimientos()->exists()) {
            return back()->with('error', 'El producto tiene movimientos de inventario; desactívalo en lugar de eliminarlo.');
        }

        $producto->delete();

        return redirect()->route('productos.index')->with('success', 'Producto eliminado.');
    }

    private function categorias()
    {
        return CategoriaProducto::where('empresa_id', $this->empresaId())
            ->where('estado', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'id');
    }

    private function validar(Request $request, ?Producto $producto = null): array
    {
        $empresaId = $this->empresaId();

        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:50',
                Rule::unique('productos', 'codigo')->where('empresa_id', $empresaId)->ignore($producto?->id)],
            'codigo_barras' => ['nullable', 'string', 'max:60'],
            'nombre' => ['required', 'string', 'max:180'],
            'categoria_producto_id' => ['nullable', Rule::exists('categorias_producto', 'id')->where('empresa_id', $empresaId)],
            'unidad_medida_id' => ['required', 'exists:catalogos,id'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio_compra' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'estado' => ['required', 'in:0,1'],
        ]);

        $data['maneja_lotes'] = $request->boolean('maneja_lotes');
        $data['maneja_vencimiento'] = $request->boolean('maneja_vencimiento');

        // Controlar vencimiento implica controlar lotes.
        if ($data['maneja_vencimiento']) {
            $data['maneja_lotes'] = true;
        }

        return $data;
    }
}
