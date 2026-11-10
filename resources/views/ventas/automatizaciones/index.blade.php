@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Automatizaciones</h5>
                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                <a href="{{ route('automatizaciones.logs') }}" class="btn btn-sm btn-info"><i class="fas fa-history"></i> Historial</a>
                @can('correos.ver') <a href="{{ route('correos.index') }}" class="btn btn-sm btn-primary"><i class="fas fa-envelope"></i> Cola de correos</a> @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <small><i class="fas fa-info-circle me-1"></i>Tareas que el sistema ejecuta solas: cierres de caja, alertas, recordatorios y respaldos.</small><br>
            <small><i class="fas fa-clock me-1"></i>Requieren el cron del servidor (<code>php artisan schedule:run</code> cada minuto). Ver LEEME.</small><br>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        @include('ventas.partials.alertas')

        @foreach ($automatizaciones as $grupo => $lista)
            <div class="card mb-3">
                <div class="card-body">
                    <h6><i class="fas fa-store me-1"></i>{{ $grupo }}</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead><tr><th>Automatización</th><th>Cuándo</th><th class="text-center">Estado</th><th>Última ejecución</th><th class="text-end">Acciones</th></tr></thead>
                            <tbody>
                                @foreach ($lista as $a)
                                    <tr>
                                        <td>{{ $a->nombre }}<br><small class="text-muted">{{ $a->descripcion }}</small></td>
                                        <td>
                                            @if ($a->frecuencia === 'evento') Al ocurrir el evento
                                            @elseif ($a->frecuencia === 'cada_hora') Cada hora
                                            @else Diaria {{ substr((string) $a->hora_ejecucion, 0, 5) }} @endif
                                        </td>
                                        <td class="text-center"><span class="badge bg-gradient-{{ $a->activo ? 'success' : 'secondary' }}">{{ $a->activo ? 'Activa' : 'Inactiva' }}</span></td>
                                        <td>{{ $a->ultima_ejecucion_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                        <td class="text-end text-nowrap">
                                            @can('automatizaciones.editar')
                                                <a href="{{ route('automatizaciones.edit', $a) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Configurar"><i class="fas fa-cog"></i></a>
                                            @endcan
                                            @can('automatizaciones.ejecutar')
                                                @if ($a->frecuencia !== 'evento')
                                                    <form method="POST" action="{{ route('automatizaciones.ejecutar', $a) }}" class="d-inline" onsubmit="return confirm('¿Ejecutar ahora?');">
                                                        @csrf
                                                        <button class="btn btn-xs btn-primary mb-0 px-2 py-1" title="Ejecutar ahora"><i class="fas fa-play"></i></button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@endsection
