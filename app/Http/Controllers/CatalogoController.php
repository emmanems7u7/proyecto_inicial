<?php
namespace App\Http\Controllers;
use App\Models\Categoria;

use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Interfaces\CatalogoInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CatalogoController extends Controller
{

    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $CatalogoInterface)
    {

        $this->CatalogoRepository = $CatalogoInterface;
    }

    public function index(Request $request)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
        ];
        $search_c = $request->get('search_c');

        $categorias = Categoria::when($search_c, function ($query, $search_c) {
            $query->where('nombre', 'like', "%{$search_c}%")
                ->orWhere('descripcion', 'like', "%{$search_c}%");
        })

            ->paginate(9, ['*'], 'categorias')
            ->withQueryString();

        $search = $request->get('search');



        $catalogos = Catalogo::with(['categoria', 'parent'])
            ->when($search, function ($query, $search) {
                return $query->where('catalogo_codigo', 'like', "%{$search}%")
                    ->orWhere('catalogo_descripcion', 'like', "%{$search}%")
                    ->orWhereHas('categoria', function ($query) use ($search) {
                        $query->where('nombre', 'like', "%{$search}%");
                    });
            })
            ->paginate(15);
        return view('catalogo.index', compact('catalogos', 'categorias', 'breadcrumb'));
    }

    public function create()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
            ['name' => 'Crear Catalogo', 'url' => route('catalogos.index')],

        ];
        $catalogos = Catalogo::all();
        $categorias = Categoria::all();
        return view('catalogo.create', compact('catalogos', 'breadcrumb', 'categorias'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'categoria' => 'required|exists:categorias,id',
            'catalogo_parent' => 'nullable|string|max:5',
            'catalogo_codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('catalogos')->where(function ($query) use ($request) {
                    return $query->where('categoria_id', $request->categoria);
                }),
            ],
            'catalogo_descripcion' => 'required|string|max:100',
            'catalogo_estado' => 'required|integer|in:0,1',
        ]);

        $this->CatalogoRepository->GuardarCatalogo($request);

        return redirect()->route('catalogos.index')->with('success', 'Catálogo creado correctamente.');
    }


    public function edit($id)
    {
        $catalogo = Catalogo::findOrFail($id);
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Catalogo', 'url' => route('catalogos.index')],
            ['name' => 'Crear Catalogo', 'url' => route('catalogos.index')],

        ];
        $catalogos = Catalogo::where('id', '!=', $catalogo->id)->get();

        $categorias = Categoria::all();

        return view('catalogo.edit', compact('id', 'catalogo', 'catalogos', 'categorias', 'breadcrumb'));
    }

    public function update(Request $request, $id)
    {

        $catalogo = Catalogo::findOrFail($id);

        $request->validate([
            'categoria' => 'required|exists:categorias,id',
            'catalogo_parent' => 'nullable|string|max:5',
            'catalogo_codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('catalogos')->where(function ($query) use ($request) {
                    return $query->where('categoria_id', $request->categoria);
                })->ignore($catalogo->id),
            ],
            'catalogo_descripcion' => 'required|string|max:100',
            'catalogo_estado' => 'required|integer|in:0,1',
        ]);

        $this->CatalogoRepository->EditarCatalogo($request, $catalogo);


        return redirect()->route('catalogos.index')->with('success', 'Catálogo actualizado correctamente.');
    }

    public function destroy($id)
    {
        $catalogo = Catalogo::findOrFail($id);

        $catalogo->delete();

        return redirect()->back()->with('success', 'Catálogo eliminado correctamente.');
    }

    public function ultimoCodigo($categoriaId)
    {
        //  extraer ultimo codigo
        $ultimo = Catalogo::where('categoria_id', $categoriaId)
            ->orderByDesc('id')
            ->value('catalogo_codigo');

        if ($ultimo) {
            return response()->json([
                'codigo' => $ultimo
            ]);
        }

        $categoria = Categoria::findOrFail($categoriaId);

        $prefijo = $this->CatalogoRepository->generarPrefijoUnico($categoria->nombre);

        return response()->json([
            'codigo' => $prefijo . '-000'
        ]);
    }

    public function storeRapido(Request $request)
    {
        $request->validate([
            'categoria' => ['required'],
            'prefijo' => ['required', 'string', 'max:10'],
            'descripcion' => ['required', 'string', 'max:150'],
        ]);

        return DB::transaction(function () use ($request) {

            // Buscar categoría por ID o nombre
            $categoria = is_numeric($request->categoria)
                ? Categoria::find($request->categoria)
                : Categoria::where('nombre', $request->categoria)->first();

            if (!$categoria || !$categoria->estado) {
                throw ValidationException::withMessages([
                    'categoria' => 'La categoría seleccionada no existe o está inactiva.',
                ]);
            }

            $prefijo = $request->prefijo;

            // Evitar duplicados dentro de la misma categoría
            $existe = Catalogo::where('categoria_id', $categoria->id)
                ->whereRaw('LOWER(catalogo_descripcion) = ?', [
                    mb_strtolower(trim($request->descripcion))
                ])
                ->where('catalogo_estado', 1)
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'descripcion' => 'Este registro ya existe en el catálogo.',
                ]);
            }

            // Obtener último código del prefijo
            $codigos = Catalogo::where('catalogo_codigo', 'like', $prefijo . '%')
                ->lockForUpdate()
                ->pluck('catalogo_codigo');

            $ultimo = $codigos->map(function ($codigo) use ($prefijo) {
                return (int) substr($codigo, strlen($prefijo));
            })->max() ?? 0;

            $codigo = $prefijo . str_pad($ultimo + 1, 3, '0', STR_PAD_LEFT);

            $catalogo = Catalogo::create([
                'categoria_id' => $categoria->id,
                'catalogo_parent' => null,
                'catalogo_codigo' => $codigo,
                'catalogo_descripcion' => trim($request->descripcion),
                'catalogo_estado' => 1,
            ]);

            return response()->json([
                'id' => $catalogo->id,
                'codigo' => $catalogo->catalogo_codigo,
                'descripcion' => $catalogo->catalogo_descripcion,
            ]);

        });
    }
}

