<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\CategoriaProducto;
use App\Models\Inventario;
use App\Models\PresentacionCatalogo;
use App\Models\Producto;
use App\Support\Catalogos;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductoController extends Controller
{
    use UsaContexto;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $tienda = TiendaContext::actual();

        // Columnas de stock: almacenes de la tienda activa (o solo el elegido en el filtro).
        $almacenes = Almacen::where('tienda_id', $tienda?->id)->where('estado', 1)->orderByDesc('es_principal')->orderBy('codigo')->get();
        $filtroAlmacen = $request->filled('almacen_id') ? $almacenes->firstWhere('id', (int) $request->input('almacen_id')) : null;
        $columnas = $filtroAlmacen ? collect([$filtroAlmacen]) : $almacenes;

        $productos = Producto::with(['categoria', 'presentaciones' => fn($p) => $p->where('estado', 1)])
            ->where('empresa_id', $this->empresaId())
            ->when($q !== '', fn($query) => $query->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")->orWhere('codigo', 'like', "%{$q}%")
                    ->orWhere('codigo_barras', 'like', "%{$q}%")->orWhere('marca', 'like', "%{$q}%")
                    // el nombre popular ayuda a encontrar el producto como lo conoce la gente
                    ->orWhereHas('presentaciones', fn($p) => $p->where('nombre_popular', 'like', "%{$q}%")->orWhere('nombre', 'like', "%{$q}%"));
            }))
            ->when($request->filled('categoria_producto_id'), fn($query) => $query->where('categoria_producto_id', $request->input('categoria_producto_id')))
            ->when($request->filled('estado'), fn($query) => $query->where('estado', (int) $request->input('estado')))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $stock = Inventario::whereIn('almacen_id', $columnas->pluck('id'))->whereIn('producto_id', $productos->pluck('id'))
            ->get()->groupBy('producto_id')->map(fn($g) => $g->pluck('cantidad', 'almacen_id'));

        $categorias = $this->categorias();

        // Edición en la misma pantalla del catálogo (?editar=ID)
        $editar = null;
        if ($request->filled('editar') && auth()->user()->can('productos.editar')) {
            $editar = Producto::where('empresa_id', $this->empresaId())->find($request->input('editar'));
        }
        $formulario = $editar ? $this->datosFormulario($editar) : [];
        $breadcrumb = $this->breadcrumb(['Productos' => route('productos.index')]);

        return view('ventas.productos.index', compact('productos', 'categorias', 'q', 'almacenes', 'columnas', 'filtroAlmacen', 'stock', 'editar', 'breadcrumb') + $formulario);
    }

    public function create()
    {
        $producto = new Producto(['estado' => 1, 'stock_minimo' => 0]);
        $breadcrumb = $this->breadcrumb(['Productos' => route('productos.index'), 'Nuevo' => null]);

        return view('ventas.productos.create', $this->datosFormulario($producto) + compact('producto', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        [$data, $filas] = $this->validar($request);

        $producto = DB::transaction(function () use ($data, $filas, $request) {
            $producto = Producto::create($data + ['empresa_id' => $this->empresaId()]);

            // Se crea primero para obtener el ID y con él se generan ambos códigos.
            $producto->update([
                'codigo' => $this->generarCodigo($producto->id),
                'codigo_barras' => $this->generarCodigoBarras($producto->id),
            ]);

            $this->sincronizarPresentaciones($producto, $filas);
            $this->guardarImagen($request, $producto);
            $this->aplicarStockMinimo($producto);

            return $producto;
        });

        if ($request->boolean('agregar_otro')) {
            return redirect()->route('productos.create')->with('status', "Producto «{$producto->nombre}» creado. Puedes agregar otro.");
        }

        return redirect()->route('productos.index')->with('status', 'Producto creado. Registra su stock inicial en Inventario > Registrar movimiento.');
    }

    public function edit(Producto $producto)
    {
        $this->autorizarEmpresa($producto);
        $breadcrumb = $this->breadcrumb(['Productos' => route('productos.index'), 'Editar' => null]);

        return view('ventas.productos.edit', $this->datosFormulario($producto) + compact('producto', 'breadcrumb'));
    }

    public function update(Request $request, Producto $producto)
    {
        $this->autorizarEmpresa($producto);
        [$data, $filas] = $this->validar($request, $producto);

        // No se puede cambiar el manejo de lotes si ya hay stock: los saldos por lote quedarían inconsistentes.
        if (
            (bool) $producto->maneja_lotes !== (bool) $data['maneja_lotes']
            && $producto->inventarios()->where('cantidad', '>', 0)->exists()
        ) {
            return back()->withInput()->with('error', 'No puedes cambiar el manejo de lotes mientras el producto tenga stock.');
        }

        DB::transaction(function () use ($producto, $data, $filas, $request) {
            // Genera los códigos únicamente si el producto todavía no los tiene.
            if (empty($producto->codigo)) {
                $data['codigo'] = $this->generarCodigo($producto->id);
            }
            if (empty($producto->codigo_barras)) {
                $data['codigo_barras'] = $this->generarCodigoBarras($producto->id);
            }

            $producto->update($data);
            $this->sincronizarPresentaciones($producto, $filas);
            $this->guardarImagen($request, $producto);
            $this->aplicarStockMinimo($producto);
        });

        return redirect()->route('productos.index')->with('status', 'Producto actualizado.');
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

    // ---------------------------------------------------------------------------------------------------------

    /** Datos que necesita el formulario (crear, editar y edición dentro del catálogo). */
    private function datosFormulario(Producto $producto): array
    {
        $filas = $producto->exists
            ? $producto->presentaciones()->reorder()->orderByDesc('es_base')->orderBy('unidades')->where('estado', 1)->get()->map(fn($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'unidades' => (float) $p->unidades,
                'precio_costo' => (float) $p->precio_costo,
                'precio_menor' => (float) $p->precio_menor,
                'precio_mayor' => (float) $p->precio_mayor,
                'precio_distribuidor' => (float) $p->precio_distribuidor,
                'nombre_popular' => $p->nombre_popular,
            ])->all()
            : [];
        if (!$filas) {
            $filas = [['id' => null, 'nombre' => 'Unidad', 'unidades' => 1, 'precio_costo' => 0, 'precio_menor' => 0, 'precio_mayor' => 0, 'precio_distribuidor' => 0, 'nombre_popular' => null]];
        }

        return [
            'categorias' => $this->categorias(),
            'unidades' => Catalogos::unidades(),
            'filasPresentaciones' => old('presentaciones', $filas),
            'catalogoPresentaciones' => PresentacionCatalogo::where('empresa_id', $this->empresaId())->where('estado', 1)->orderBy('unidades')->pluck('unidades', 'nombre'),
        ];
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
        $v = $request->validate([
            'nombre' => ['required', 'string', 'max:180'],
            'marca' => ['nullable', 'string', 'max:80'],
            'categoria_producto_id' => ['nullable', Rule::exists('categorias_producto', 'id')->where('empresa_id', $empresaId)],
            'unidad_medida_id' => ['required', 'exists:catalogos,id'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'stock_minimo' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'estado' => ['required', 'in:0,1'],
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'presentaciones' => ['required', 'array', 'min:1'],
            'presentaciones.*.id' => ['nullable', 'integer'],
            'presentaciones.*.nombre' => ['required', 'string', 'max:60'],
            'presentaciones.*.unidades' => ['required', 'numeric', 'gt:0', 'max:99999'],
            'presentaciones.*.precio_costo' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'presentaciones.*.precio_menor' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'presentaciones.*.precio_mayor' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'presentaciones.*.precio_distribuidor' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'presentaciones.*.nombre_popular' => ['nullable', 'string', 'max:120'],
        ], [
            'presentaciones.*.nombre.required' => 'Cada presentación necesita un nombre.',
            'presentaciones.*.unidades.gt' => 'Las unidades de cada presentación deben ser mayores a cero.',
            'presentaciones.*.precio_costo.required' => 'Completa el costo de todas las presentaciones.',
            'presentaciones.*.precio_menor.required' => 'Completa el precio menor de todas las presentaciones.',
            'presentaciones.*.precio_mayor.required' => 'Completa el precio mayorista de todas las presentaciones.',
            'presentaciones.*.precio_distribuidor.required' => 'Completa el precio distribuidor de todas las presentaciones.',
        ]);

        $filas = array_values($v['presentaciones']);
        $filas[0]['unidades'] = 1; // la primera fila es siempre la unidad base del stock
        $nombres = collect($filas)->map(fn($f) => mb_strtolower(trim($f['nombre'])));
        if ($nombres->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['presentaciones' => 'No repitas el nombre de una presentación en el mismo producto.']);
        }

        $data = collect($v)->only(['nombre', 'marca', 'categoria_producto_id', 'unidad_medida_id', 'descripcion', 'estado'])->all();
        $data['stock_minimo'] = (float) ($v['stock_minimo'] ?? 0);
        $data['maneja_lotes'] = $request->boolean('maneja_lotes');
        $data['maneja_vencimiento'] = $request->boolean('maneja_vencimiento');
        // Controlar vencimiento implica controlar lotes.
        if ($data['maneja_vencimiento']) {
            $data['maneja_lotes'] = true;
        }
        // Los precios "generales" del producto salen de la presentación base (los usan reportes y valorización).
        $data['precio_compra'] = (float) $filas[0]['precio_costo'];
        $data['precio_venta'] = (float) $filas[0]['precio_menor'];

        return [$data, $filas];
    }

    private function sincronizarPresentaciones(Producto $producto, array $filas): void
    {
        $existentes = $producto->presentaciones()->reorder()->get()->keyBy('id');
        $mantener = [];

        foreach ($filas as $k => $f) {
            $attrs = [
                'nombre' => trim($f['nombre']),
                'unidades' => $k === 0 ? 1 : (float) $f['unidades'],
                'es_base' => $k === 0,
                'precio_costo' => $f['precio_costo'],
                'precio_menor' => $f['precio_menor'],
                'precio_mayor' => $f['precio_mayor'],
                'precio_distribuidor' => $f['precio_distribuidor'],
                'nombre_popular' => ($f['nombre_popular'] ?? null) ?: null,
                'estado' => 1,
            ];
            $pres = !empty($f['id']) ? $existentes->get((int) $f['id']) : null;
            $pres ? $pres->update($attrs) : ($pres = $producto->presentaciones()->create($attrs));
            $mantener[] = $pres->id;

            // El catálogo de presentaciones de la empresa crece con los nombres nuevos.
            if ($k > 0) {
                PresentacionCatalogo::firstOrCreate(
                    ['empresa_id' => $producto->empresa_id, 'nombre' => $attrs['nombre']],
                    ['unidades' => $attrs['unidades'], 'estado' => 1]
                );
            }
        }

        foreach ($existentes as $id => $pres) {
            if (in_array($id, $mantener, true)) {
                continue;
            }
            $usada = DB::table('venta_detalles')->where('presentacion_id', $id)->exists()
                || DB::table('compra_detalles')->where('presentacion_id', $id)->exists();
            // Si ya se vendió o compró con ella, se desactiva para conservar el historial.
            $usada ? $pres->update(['estado' => 0, 'es_base' => false, 'nombre' => mb_substr($pres->nombre, 0, 45) . " (inactiva {$id})"]) : $pres->delete();
        }

        $base = $filas[0];
        $producto->update(['precio_compra' => $base['precio_costo'], 'precio_venta' => $base['precio_menor']]);
    }

    /** La foto se guarda en public/imagenes/productos (restricción del cPanel). */
    private function guardarImagen(Request $request, Producto $producto): void
    {
        $eliminar = $request->boolean('eliminar_imagen') || $request->boolean('quitar_imagen');
        if (!$request->hasFile('imagen') && !$eliminar) {
            return;
        }

        // Eliminar la imagen anterior (si se reemplaza o se quita).
        if ($producto->imagen) {
            $anterior = public_path($producto->imagen);
            if (File::exists($anterior)) {
                File::delete($anterior);
            }
        }

        if ($request->hasFile('imagen')) {
            $directorio = public_path('imagenes/productos');
            if (!File::exists($directorio)) {
                File::makeDirectory($directorio, 0755, true);
            }
            $archivo = $request->file('imagen');
            $nombre = uniqid('producto_') . '.' . $archivo->getClientOriginalExtension();
            $archivo->move($directorio, $nombre);
            $producto->update(['imagen' => 'imagenes/productos/' . $nombre]);
        } else {
            $producto->update(['imagen' => null]);
        }
    }

    /** El stock mínimo del producto es el valor por defecto de los almacenes que aún no tienen uno propio. */
    private function aplicarStockMinimo(Producto $producto): void
    {
        if ((float) $producto->stock_minimo > 0) {
            Inventario::where('producto_id', $producto->id)->where('stock_minimo', 0)->update(['stock_minimo' => $producto->stock_minimo]);
        }
    }

    /** Código interno: P-001, P-002, P-003... */
    private function generarCodigo(int $id): string
    {
        return 'P-' . str_pad((string) $id, 3, '0', STR_PAD_LEFT);
    }

    /** Código EAN-13 de uso interno (prefijo 20 + ID + dígito de control). */
    private function generarCodigoBarras(int $id): string
    {
        $base = '20' . str_pad((string) $id, 10, '0', STR_PAD_LEFT);
        $suma = 0;
        for ($i = 0; $i < 12; $i++) {
            $suma += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return $base . ((10 - ($suma % 10)) % 10);
    }
}
