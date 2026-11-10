<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\CuentaPorCobrar;
use App\Models\Cliente;
use App\Services\CajaService;
use App\Services\CuentaService;
use App\Support\Catalogos;
use App\Support\TiendaContext;
use Illuminate\Http\Request;

class CuentaPorCobrarController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private CuentaService $cuentas, private CajaService $caja)
    {
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $estado = $request->input('estado', 'abiertas');
        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');

        $base = CuentaPorCobrar::query()
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($q !== '', fn ($c) => $c->where(function ($w) use ($q) {
                $w->where('numero_documento', 'like', "%{$q}%")->orWhereHas('cliente', fn ($t) => $t->where('nombre', 'like', "%{$q}%"));
            }))
            ->when($request->filled('tienda_id'), fn ($c) => $c->where('tienda_id', $request->input('tienda_id')))
            ->when($request->filled('desde'), fn ($c) => $c->whereDate('fecha_vencimiento', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($c) => $c->whereDate('fecha_vencimiento', '<=', $request->input('hasta')));

        $filtrada = (clone $base)
            ->when($estado === 'abiertas', fn ($c) => $c->whereNotIn('estado', ['pagada', 'anulada']))
            ->when($estado === 'vencidas', fn ($c) => $c->whereNotIn('estado', ['pagada', 'anulada'])->where('saldo', '>', 0)->whereDate('fecha_vencimiento', '<', today()->toDateString()))
            ->when($estado === 'pagadas', fn ($c) => $c->where('estado', 'pagada'));

        $resumen = [
            'saldo' => (float) (clone $base)->whereNotIn('estado', ['pagada', 'anulada'])->sum('saldo'),
            'vencido' => (float) (clone $base)->whereNotIn('estado', ['pagada', 'anulada'])->where('saldo', '>', 0)->whereDate('fecha_vencimiento', '<', today()->toDateString())->sum('saldo'),
        ];

        $cuentas = $filtrada->with(['cliente', 'tienda'])->orderBy('fecha_vencimiento')->orderBy('id')->paginate(15)->withQueryString();
        $breadcrumb = $this->breadcrumb(['Cuentas por cobrar' => route('cxc.index')]);

        return view('ventas.cxc.index', compact('cuentas', 'tiendas', 'resumen', 'estado', 'q', 'breadcrumb'));
    }

    public function show(CuentaPorCobrar $cuenta)
    {
        $this->autorizarTienda($cuenta->tienda_id);
        $cuenta->load(['cliente', 'tienda', 'venta', 'pagos.metodoPago', 'pagos.user']);

        $metodos = Catalogos::metodosPago();
        $efectivoId = $this->caja->metodoEfectivoId();
        $sesion = $this->caja->sesionAbiertaDeUsuario(auth()->id(), $cuenta->tienda_id);
        $breadcrumb = $this->breadcrumb(['Cuentas por cobrar' => route('cxc.index'), $cuenta->numero_documento => null]);

        return view('ventas.cxc.show', compact('cuenta', 'metodos', 'efectivoId', 'sesion', 'breadcrumb'));
    }

    public function pagoStore(Request $request, CuentaPorCobrar $cuenta)
    {
        $this->autorizarTienda($cuenta->tienda_id);

        $data = $request->validate([
            'monto' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'metodo_pago_id' => ['required', 'exists:catalogos,id'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'observacion' => ['nullable', 'string', 'max:255'],
        ]);

        // Si el usuario tiene caja abierta en la tienda, el movimiento queda registrado en ella.
        $sesion = $this->caja->sesionAbiertaDeUsuario(auth()->id(), $cuenta->tienda_id);

        try {
            $this->cuentas->pagarCxc($cuenta, (float) $data['monto'], (int) $data['metodo_pago_id'], auth()->id(), $data['referencia'] ?? null, $data['observacion'] ?? null, $sesion);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('cxc.show', $cuenta)->with('success', 'Pago registrado.');
    }

    /** Estado de cuenta de un cliente (todas sus tiendas permitidas o una en particular). */
    public function estadoCuenta(Request $request, Cliente $cliente)
    {
        $this->autorizarEmpresa($cliente);

        $cuentas = CuentaPorCobrar::with(['tienda', 'pagos'])
            ->where('cliente_id', $cliente->id)
            ->whereIn('tienda_id', TiendaContext::ids())
            ->when($request->filled('tienda_id'), fn ($c) => $c->where('tienda_id', $request->input('tienda_id')))
            ->where('estado', '!=', 'anulada')
            ->orderBy('fecha_emision')->orderBy('id')
            ->get();

        $tiendas = TiendaContext::permitidas()->pluck('nombre', 'id');
        $breadcrumb = $this->breadcrumb(['Cuentas por cobrar' => route('cxc.index'), 'Estado de cuenta' => null]);

        return view('ventas.cxc.estado_cuenta', ['cliente' => $cliente, 'cuentas' => $cuentas, 'tiendas' => $tiendas, 'breadcrumb' => $breadcrumb]);
    }
}
