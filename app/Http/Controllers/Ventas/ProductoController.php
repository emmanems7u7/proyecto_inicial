<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use App\Models\Producto;
use App\Support\Catalogos;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
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
            ->when($request->filled('categoria_producto_id'), fn($query) => $query->where('categoria_producto_id', $request->input('categoria_producto_id')))
            ->when($request->filled('estado'), fn($query) => $query->where('estado', (int) $request->input('estado')))
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
        $data = $this->validar($request);

        // Crear primero el producto para obtener su ID.
        $producto = Producto::create($data + [
            'empresa_id' => $this->empresaId(),
        ]);

        // Generar automáticamente ambos códigos.
        $producto->update([
            'codigo' => $this->generarCodigo($producto->id),
            'codigo_barras' => $this->generarCodigoBarras($producto->id),
        ]);

        return redirect()->route('productos.index')
            ->with('status', 'Producto creado. Registra su stock inicial en Inventario > Registrar movimiento.');
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

        // No permitir cambiar el manejo de lotes si existe stock.
        if (
            (bool) $producto->maneja_lotes !== (bool) $data['maneja_lotes']
            && $producto->inventarios()->where('cantidad', '>', 0)->exists()
        ) {
            return back()->withInput()->with(
                'error',
                'No puedes cambiar el manejo de lotes mientras el producto tenga stock.'
            );
        }

        // Determinar si se debe eliminar o reemplazar la imagen.
        if ($request->boolean('eliminar_imagen') || $request->hasFile('imagen')) {

            // Eliminar imagen anterior.
            if ($producto->imagen) {
                $rutaAnterior = public_path($producto->imagen);

                if (File::exists($rutaAnterior)) {
                    File::delete($rutaAnterior);
                }
            }

            if (!$request->hasFile('imagen')) {
                $data['imagen'] = null;
            }
        }

        // Generar códigos únicamente si el producto todavía no los tiene.
        if (empty($producto->codigo)) {
            $data['codigo'] = $this->generarCodigo($producto->id);
        }

        if (empty($producto->codigo_barras)) {
            $data['codigo_barras'] = $this->generarCodigoBarras($producto->id);
        }

        $producto->update($data);

        return redirect()->route('productos.index')
            ->with('status', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        $this->autorizarEmpresa($producto);

        if ($producto->movimientos()->exists()) {
            return back()->with('error', 'El producto tiene movimientos de inventario; desactívalo en lugar de eliminarlo.');
        }

        $producto->delete();

        return redirect()->route('productos.index')->with('status', 'Producto eliminado.');
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


            'nombre' => ['required', 'string', 'max:180'],
            'categoria_producto_id' => ['nullable', Rule::exists('categorias_producto', 'id')->where('empresa_id', $empresaId)],
            'unidad_medida_id' => ['required', 'exists:catalogos,id'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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

        // Guardar imagen en public/imagenes/productos
        if ($request->hasFile('imagen')) {

            $directorio = public_path('imagenes/productos');

            if (!file_exists($directorio)) {
                mkdir($directorio, 0755, true);
            }

            $archivo = $request->file('imagen');

            $nombre = uniqid('producto_') . '.' . $archivo->getClientOriginalExtension();

            $archivo->move($directorio, $nombre);

            $data['imagen'] = 'imagenes/productos/' . $nombre;
        }

        return $data;
    }
    /**
     * Genera el código interno del producto.
     * Ejemplo: P-001, P-002, P-003.
     */
    private function generarCodigo(int $id): string
    {
        return 'P-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
    }
    /**
     * Genera un código EAN-13 de uso interno.
     */
    private function generarCodigoBarras(int $id): string
    {
        // Prefijo interno 20 + identificador del producto.
        $base = '20' . str_pad((string) $id, 10, '0', STR_PAD_LEFT);

        // Calcular dígito de control EAN-13.
        $suma = 0;

        for ($i = 0; $i < 12; $i++) {
            $suma += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $digitoControl = (10 - ($suma % 10)) % 10;

        return $base . $digitoControl;
    }
}
