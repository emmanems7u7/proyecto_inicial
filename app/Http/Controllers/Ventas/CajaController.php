<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaSesion;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Configuración de cajas (puntos de cobro) por tienda. */
class CajaController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');

        $cajas = Caja::with('tienda')
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($request->filled('tienda_id'), fn ($query) => $query->where('tienda_id', $request->input('tienda_id')))
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('nombre', 'like', "%{$q}%")->orWhere('codigo', 'like', "%{$q}%")))
            ->orderBy('tienda_id')->orderBy('nombre')
            ->paginate(15)->withQueryString();

        $breadcrumb = $this->breadcrumb(['Cajas' => route('cajas.index')]);

        return view('ventas.cajas.index', compact('cajas', 'tiendas', 'q', 'breadcrumb'));
    }

    public function create()
    {
        $caja = new Caja(['estado' => 1, 'tienda_id' => TiendaContext::actual()?->id]);
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $breadcrumb = $this->breadcrumb(['Cajas' => route('cajas.index'), 'Nueva' => null]);

        return view('ventas.cajas.create', compact('caja', 'tiendas', 'breadcrumb'));
    }

    public function store(Request $request)
    {
        Caja::create($this->validar($request));

        return redirect()->route('cajas.index')->with('success', 'Caja creada.');
    }

    public function edit(Caja $caja)
    {
        $this->autorizarTienda($caja->tienda_id);
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $breadcrumb = $this->breadcrumb(['Cajas' => route('cajas.index'), 'Editar' => null]);

        return view('ventas.cajas.edit', compact('caja', 'tiendas', 'breadcrumb'));
    }

    public function update(Request $request, Caja $caja)
    {
        $this->autorizarTienda($caja->tienda_id);
        $data = $this->validar($request, $caja);

        if ((int) $data['tienda_id'] !== (int) $caja->tienda_id && $caja->sesiones()->exists()) {
            return back()->withInput()->with('error', 'No puedes mover de tienda una caja con historial.');
        }

        $caja->update($data);

        return redirect()->route('cajas.index')->with('success', 'Caja actualizada.');
    }

    public function destroy(Caja $caja)
    {
        $this->autorizarTienda($caja->tienda_id);

        if ($caja->sesiones()->exists()) {
            return back()->with('error', 'La caja tiene historial de aperturas; desactívala en lugar de eliminarla.');
        }

        $caja->delete();

        return redirect()->route('cajas.index')->with('success', 'Caja eliminada.');
    }

    private function validar(Request $request, ?Caja $caja = null): array
    {
        return $request->validate([
            'tienda_id' => ['required', Rule::in(TiendaContext::ids())],
            'codigo' => ['required', 'string', 'max:20',
                Rule::unique('cajas', 'codigo')->where('tienda_id', $request->input('tienda_id'))->ignore($caja?->id)],
            'nombre' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'in:0,1'],
        ]);
    }
}
