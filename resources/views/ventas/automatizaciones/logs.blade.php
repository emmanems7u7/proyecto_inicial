@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Historial de automatizaciones</h5>
                    <a href="{{ route('automatizaciones.index') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Cada ejecución queda registrada con su resultado y
                        detalle.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">
                    @include('ventas.partials.alertas')
                    <form method="GET" action="{{ route('automatizaciones.logs') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <select name="codigo" class="form-select form-select-sm">
                                <option value="">Todas las automatizaciones</option>
                                @foreach ($codigos as $codigo => $nombre)
                                    <option value="{{ $codigo }}" @selected(request('codigo') === $codigo)>{{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los resultados</option>
                                <option value="exitoso" @selected(request('estado') === 'exitoso')>Exitosos</option>
                                <option value="omitido" @selected(request('estado') === 'omitido')>Omitidos</option>
                                <option value="error" @selected(request('estado') === 'error')>Con error</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="date" name="desde" value="{{ request('desde') }}"
                                class="form-control form-control-sm" title="Desde"></div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('automatizaciones.logs') }}"
                                class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover small-table">
                            <thead>
                                <tr>
                                    <th>Inicio</th>
                                    <th>Automatización</th>
                                    <th>Tienda</th>
                                    <th class="text-center">Resultado</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $l)
                                    <tr>
                                        <td class="text-nowrap">{{ $l->iniciada_at->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $l->automatizacion->nombre ?? '—' }}</td>
                                        <td>{{ $l->tienda->nombre ?? 'Global' }}</td>
                                        <td class="text-center"><span
                                                class="badge bg-gradient-{{ ['exitoso' => 'success', 'omitido' => 'secondary', 'error' => 'danger'][$l->estado] ?? 'secondary' }}">{{ ucfirst($l->estado) }}</span>
                                        </td>
                                        <td>
                                            {{ $l->mensaje }}
                                            @if ($l->detalle)
                                                <details>
                                                    <summary class="text-muted" style="cursor: pointer;">
                                                        <small>detalle</small></summary>
                                                    <pre class="mb-0" style="font-size: 11px; white-space: pre-wrap;">{{ json_encode($l->detalle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                </details>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Sin ejecuciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $logs->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
