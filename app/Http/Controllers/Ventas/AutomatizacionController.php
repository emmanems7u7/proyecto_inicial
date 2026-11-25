<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Concerns\UsaContexto;
use App\Http\Controllers\Concerns\UsaOperaciones;
use App\Http\Controllers\Controller;
use App\Models\Automatizacion;
use App\Models\AutomatizacionLog;
use App\Models\ColaCorreo;
use App\Services\AutomatizacionCatalogo;
use App\Services\AutomatizacionService;
use App\Services\CorreoService;
use App\Support\TiendaContext;
use Illuminate\Http\Request;

class AutomatizacionController extends Controller
{
    use UsaContexto, UsaOperaciones;

    public function __construct(private AutomatizacionService $servicio, private CorreoService $correos)
    {
    }

    public function index()
    {
        $this->servicio->asegurarConfiguracion(TiendaContext::permitidas());

        $automatizaciones = Automatizacion::with('tienda')
            ->where(fn($q) => $q->whereNull('tienda_id')->orWhereIn('tienda_id', TiendaContext::ids()))
            ->orderByRaw('tienda_id IS NULL DESC')->orderBy('tienda_id')->orderBy('hora_ejecucion')->orderBy('codigo')
            ->get()->groupBy(fn($a) => $a->tienda?->nombre ?? 'Globales (todo el sistema)');

        $breadcrumb = $this->breadcrumb(['Automatizaciones' => route('automatizaciones.index')]);

        return view('ventas.automatizaciones.index', compact('automatizaciones', 'breadcrumb'));
    }

    public function edit(Automatizacion $automatizacion)
    {
        $this->autorizar($automatizacion);
        $esquema = AutomatizacionCatalogo::definiciones()[$automatizacion->codigo]['parametros'] ?? [];
        $breadcrumb = $this->breadcrumb(['Automatizaciones' => route('automatizaciones.index'), 'Configurar' => null]);

        return view('ventas.automatizaciones.edit', compact('automatizacion', 'esquema', 'breadcrumb'));
    }

    public function update(Request $request, Automatizacion $automatizacion)
    {
        $this->autorizar($automatizacion);
        $esquema = AutomatizacionCatalogo::definiciones()[$automatizacion->codigo]['parametros'] ?? [];

        $data = $request->validate([
            'hora_ejecucion' => ['nullable', 'date_format:H:i'],
            'parametros' => ['nullable', 'array'],
        ]);

        $parametros = [];
        foreach ($esquema as $clave => [$etiqueta, $tipo, $defecto]) {
            $valor = $request->input("parametros.{$clave}");
            $parametros[$clave] = match ($tipo) {
                'bool' => $request->boolean("parametros.{$clave}"),
                'int' => is_numeric($valor) ? max(0, (int) $valor) : $defecto,
                default => trim((string) $valor),
            };
        }

        $automatizacion->update([
            'activo' => $request->boolean('activo'),
            'hora_ejecucion' => $automatizacion->frecuencia === 'diaria' && !empty($data['hora_ejecucion']) ? $data['hora_ejecucion'] . ':00' : $automatizacion->hora_ejecucion,
            'parametros' => $parametros,
        ]);

        return redirect()->route('automatizaciones.index')->with('success', 'Automatización actualizada.');
    }

    public function ejecutar(Automatizacion $automatizacion)
    {
        $this->autorizar($automatizacion);

        if ($automatizacion->frecuencia === 'evento') {
            return back()->with('error', 'Esta automatización se dispara sola cuando ocurre el evento.');
        }

        $log = $this->servicio->ejecutar($automatizacion);

        return back()->with($log->estado === 'error' ? 'error' : 'status', "{$automatizacion->nombre}: " . ($log->mensaje ?? $log->estado));
    }

    public function logs(Request $request)
    {
        $logs = AutomatizacionLog::with(['automatizacion', 'tienda'])
            ->where(fn($q) => $q->whereNull('tienda_id')->orWhereIn('tienda_id', TiendaContext::ids()))
            ->when($request->filled('estado'), fn($q) => $q->where('estado', $request->input('estado')))
            ->when($request->filled('codigo'), fn($q) => $q->whereHas('automatizacion', fn($a) => $a->where('codigo', $request->input('codigo'))))
            ->when($request->filled('desde'), fn($q) => $q->whereDate('iniciada_at', '>=', $request->input('desde')))
            ->orderByDesc('iniciada_at')->orderByDesc('id')
            ->paginate(20)->withQueryString();

        $codigos = collect(AutomatizacionCatalogo::definiciones())->map(fn($d) => $d['nombre'])->all();
        $breadcrumb = $this->breadcrumb(['Automatizaciones' => route('automatizaciones.index'), 'Historial' => null]);

        return view('ventas.automatizaciones.logs', compact('logs', 'codigos', 'breadcrumb'));
    }

    public function correos(Request $request)
    {
        $q = trim((string) $request->input('q'));

        $correos = ColaCorreo::where(fn($c) => $c->whereNull('tienda_id')->orWhereIn('tienda_id', TiendaContext::ids()))
            ->when($q !== '', fn($c) => $c->where(fn($w) => $w->where('destinatario', 'like', "%{$q}%")->orWhere('asunto', 'like', "%{$q}%")))
            ->when($request->filled('estado'), fn($c) => $c->where('estado', $request->input('estado')))
            ->orderByDesc('id')->paginate(20)->withQueryString();

        $breadcrumb = $this->breadcrumb(['Automatizaciones' => route('automatizaciones.index'), 'Cola de correos' => null]);

        return view('ventas.automatizaciones.correos', compact('correos', 'q', 'breadcrumb'));
    }

    public function correoReintentar(ColaCorreo $correo)
    {
        abort_unless($correo->tienda_id === null || in_array((int) $correo->tienda_id, TiendaContext::ids(), true), 403);
        $this->correos->reintentar($correo);

        return back()->with('status', 'El correo volvió a la cola.');
    }

    public function correosEnviar()
    {
        ColaCorreo::where('estado', 'pendiente')->each(fn($c) => app(CorreoService::class)->despachar($c));

        return back()->with('status', "Correos enviados.");
    }

    private function autorizar(Automatizacion $automatizacion): void
    {
        abort_unless($automatizacion->tienda_id === null || in_array((int) $automatizacion->tienda_id, TiendaContext::ids(), true), 403);
    }
}
