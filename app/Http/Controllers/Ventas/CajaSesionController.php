<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\CajaSesion;
use App\Models\Catalogo;
use App\Services\CajaService;
use App\Support\Catalogos;
use App\Support\TiendaContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Apertura, movimientos y cierre de caja. */
class CajaSesionController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private CajaService $caja)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $sesiones = CajaSesion::with(['caja.tienda', 'usuarioApertura', 'usuarioCierre'])
            ->whereHas('caja', fn ($q) => $q->whereIn('tienda_id', TiendaContext::ids()))
            ->when(! $user->can('caja.ver_todas'), fn ($q) => $q->where('user_apertura_id', $user->id))
            ->when($request->filled('caja_id'), fn ($q) => $q->where('caja_id', $request->input('caja_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('desde'), fn ($q) => $q->whereDate('fecha_apertura', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->whereDate('fecha_apertura', '<=', $request->input('hasta')))
            ->orderByDesc('fecha_apertura')
            ->paginate(15)->withQueryString();

        $cajas = Caja::whereIn('tienda_id', TiendaContext::ids())->orderBy('nombre')->pluck('nombre', 'id');
        $miSesion = $this->caja->sesionAbiertaDeUsuario($user->id, $this->tiendaActual()->id);
        $breadcrumb = $this->breadcrumb(['Caja' => route('caja.sesiones.index')]);

        return view('ventas.caja.sesiones', compact('sesiones', 'cajas', 'miSesion', 'breadcrumb'));
    }

    public function abrirCreate()
    {
        $tienda = $this->tiendaActual();
        $cajas = Caja::where('tienda_id', $tienda->id)->where('estado', 1)
            ->whereDoesntHave('sesiones', fn ($q) => $q->where('estado', 'abierta'))
            ->orderBy('nombre')->pluck('nombre', 'id');
        $breadcrumb = $this->breadcrumb(['Caja' => route('caja.sesiones.index'), 'Abrir caja' => null]);

        return view('ventas.caja.abrir', compact('tienda', 'cajas', 'breadcrumb'));
    }

    public function abrirStore(Request $request)
    {
        $tienda = $this->tiendaActual();
        $data = $request->validate([
            'caja_id' => ['required', Rule::exists('cajas', 'id')->where('tienda_id', $tienda->id)],
            'monto_apertura' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $sesion = $this->caja->abrir(Caja::findOrFail($data['caja_id']), (float) $data['monto_apertura'], auth()->id(), $data['observaciones'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('caja.sesiones.show', $sesion)->with('success', 'Caja abierta.');
    }

    public function show(CajaSesion $sesion)
    {
        $this->autorizarSesion($sesion);
        $sesion->load(['caja.tienda', 'usuarioApertura', 'usuarioCierre']);

        $movimientos = CajaMovimiento::with(['metodoPago', 'user'])
            ->where('caja_sesion_id', $sesion->id)
            ->orderByDesc('fecha')->orderByDesc('id')
            ->paginate(20);

        $porMetodo = $this->caja->totalesPorMetodo($sesion);
        $metodos = Catalogo::whereIn('id', array_keys($porMetodo))->pluck('catalogo_descripcion', 'id');
        $esperado = $sesion->estado === 'abierta' ? $this->caja->esperadoEfectivo($sesion) : (float) $sesion->monto_esperado;
        $breadcrumb = $this->breadcrumb(['Caja' => route('caja.sesiones.index'), 'Detalle' => null]);

        return view('ventas.caja.show', compact('sesion', 'movimientos', 'porMetodo', 'metodos', 'esperado', 'breadcrumb'));
    }

    public function cerrarCreate(CajaSesion $sesion)
    {
        $this->autorizarSesion($sesion);
        abort_unless($sesion->estado === 'abierta', 404);

        $sesion->load('caja.tienda');
        $esperado = $this->caja->esperadoEfectivo($sesion);
        $breadcrumb = $this->breadcrumb(['Caja' => route('caja.sesiones.index'), 'Cerrar caja' => null]);

        return view('ventas.caja.cerrar', compact('sesion', 'esperado', 'breadcrumb'));
    }

    public function cerrarStore(Request $request, CajaSesion $sesion)
    {
        $this->autorizarSesion($sesion);

        $data = $request->validate([
            'monto_real' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->caja->cerrar($sesion, (float) $data['monto_real'], 'manual', auth()->id(), $data['observaciones'] ?? null);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('caja.sesiones.show', $sesion)->with('success', 'Caja cerrada.');
    }

    public function movimientoCreate(CajaSesion $sesion)
    {
        $this->autorizarSesion($sesion);
        abort_unless($sesion->estado === 'abierta', 404);

        $metodos = Catalogos::metodosPago();
        $breadcrumb = $this->breadcrumb(['Caja' => route('caja.sesiones.index'), 'Movimiento manual' => null]);

        return view('ventas.caja.movimiento', compact('sesion', 'metodos', 'breadcrumb'));
    }

    public function movimientoStore(Request $request, CajaSesion $sesion)
    {
        $this->autorizarSesion($sesion);

        $data = $request->validate([
            'tipo' => ['required', 'in:ingreso,egreso'],
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'metodo_pago_id' => ['required', 'exists:catalogos,id'],
        ]);

        try {
            $this->caja->registrarMovimiento($sesion, $data['tipo'], $data['concepto'], (float) $data['monto'], (int) $data['metodo_pago_id'], null, auth()->id());
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('caja.sesiones.show', $sesion)->with('success', 'Movimiento registrado.');
    }

    /** Solo ve/opera sesiones de sus tiendas; sin "caja.ver_todas" únicamente las propias. */
    private function autorizarSesion(CajaSesion $sesion): void
    {
        $sesion->loadMissing('caja');
        abort_unless(in_array((int) $sesion->caja->tienda_id, TiendaContext::ids(), true), 403);
        abort_unless(auth()->user()->can('caja.ver_todas') || (int) $sesion->user_apertura_id === (int) auth()->id(), 403);
    }
}
