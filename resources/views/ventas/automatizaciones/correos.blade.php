@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Cola de correos</h5>
                    <a href="{{ route('automatizaciones.index') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('correos.reenviar')
                        <form method="POST" action="{{ route('correos.enviar') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i> Enviar pendientes
                                ahora</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Los correos se envían en segundo plano con hasta 3
                        reintentos. Configura el correo saliente en el <code>.env</code> (MAIL_*).</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">
                    @include('ventas.partials.alertas')
                    <form method="GET" action="{{ route('correos.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3"><input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Destinatario o asunto..."></div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                                <option value="enviado" @selected(request('estado') === 'enviado')>Enviados</option>
                                <option value="error" @selected(request('estado') === 'error')>Con error</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('correos.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover small-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Destinatario</th>
                                    <th>Asunto</th>
                                    <th class="text-center">Estado</th>
                                    <th>Intentos</th>
                                    <th>Detalle</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($correos as $c)
                                    <tr>
                                        <td class="text-nowrap">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $c->destinatario }}</td>
                                        <td>{{ $c->asunto }}</td>
                                        <td class="text-center"><span
                                                class="badge bg-gradient-{{ ['pendiente' => 'warning', 'enviado' => 'success', 'error' => 'danger'][$c->estado] ?? 'secondary' }}">{{ ucfirst($c->estado) }}</span>
                                        </td>
                                        <td class="text-center">{{ $c->intentos }}</td>
                                        <td><small
                                                class="text-muted">{{ $c->enviado_at ? 'Enviado ' . $c->enviado_at->format('d/m H:i') : \Illuminate\Support\Str::limit($c->error, 80) }}</small>
                                        </td>
                                        <td class="text-end">
                                            @if ($c->estado !== 'enviado')
                                                @can('correos.reenviar')
                                                    <form method="POST" action="{{ route('correos.reintentar', $c) }}"
                                                        class="d-inline">
                                                        @csrf
                                                        <button class="btn btn-xs btn-warning mb-0 px-2 py-1"
                                                            title="Reintentar"><i class="fas fa-redo"></i></button>
                                                    </form>
                                                @endcan
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">La cola está vacía.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $correos->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
