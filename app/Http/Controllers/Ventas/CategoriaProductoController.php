<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaProductoController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $categorias = CategoriaProducto::with('padre')
            ->withCount('productos')
            ->where('empresa_id', $this->empresaId())
            ->when($q !== '', fn ($query) => $query->where('nombre', 'like', "%{$q}%"))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $breadcrumb = $this->breadcrumb(['Categorías de producto' => route('categorias_producto.index')]);

        return view('ventas.categorias_producto.index', compact('categorias', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $categoria = new CategoriaProducto(['estado' => 1]);
        $padres = $this->padres();
        $breadcrumb = $this->breadcrumb(['Categorías de producto' => route('categorias_producto.index'), 'Nueva' => null]);

        return view('ventas.categorias_producto.create', compact('categoria', 'padres', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        CategoriaProducto::create($this->validar($request) + ['empresa_id' => $this->empresaId()]);

        return redirect()->route('categorias_producto.index')->with('success', 'Categoría creada.');
    }

    public function edit(CategoriaProducto $categoria)
    {
        $this->autorizarEmpresa($categoria);
        $padres = $this->padres($categoria);
        $breadcrumb = $this->breadcrumb(['Categorías de producto' => route('categorias_producto.index'), 'Editar' => null]);

        return view('ventas.categorias_producto.edit', compact('categoria', 'padres', 'breadcrumb'));
    }

    public function update(Request $request, CategoriaProducto $categoria)
    {
        $this->autorizarEmpresa($categoria);
        $categoria->update($this->validar($request, $categoria));

        return redirect()->route('categorias_producto.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(CategoriaProducto $categoria)
    {
        $this->autorizarEmpresa($categoria);

        if ($categoria->productos()->exists() || $categoria->hijas()->exists()) {
            return back()->with('error', 'La categoría tiene productos o subcategorías; no se puede eliminar.');
        }

        $categoria->delete();

        return redirect()->route('categorias_producto.index')->with('success', 'Categoría eliminada.');
    }

    private function padres(?CategoriaProducto $actual = null)
    {
        return CategoriaProducto::where('empresa_id', $this->empresaId())
            ->where('estado', 1)
            ->when($actual, fn ($q) => $q->where('id', '!=', $actual->id))
            ->orderBy('nombre')
            ->pluck('nombre', 'id');
    }

    private function validar(Request $request, ?CategoriaProducto $categoria = null): array
    {
        $empresaId = $this->empresaId();

        return $request->validate([
            'nombre' => ['required', 'string', 'max:100',
                Rule::unique('categorias_producto', 'nombre')->where('empresa_id', $empresaId)->ignore($categoria?->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', Rule::exists('categorias_producto', 'id')->where('empresa_id', $empresaId),
                Rule::notIn(array_filter([$categoria?->id]))],
            'estado' => ['required', 'in:0,1'],
        ]);
    }
}
