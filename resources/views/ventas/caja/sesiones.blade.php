@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Caja - Sesiones</h5>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @if ($miSesion)
                        <a href="{{ route('caja.sesiones.show', $miSesion) }}" class="btn btn-sm btn-success"><i
                                class="fas fa-cash-register"></i> Mi caja abierta</a>
                    @else
                        @can('caja.abrir')
                            <a href="{{ route('caja.abrir.create') }}" class="btn btn-sm btn-primary"><i
                                    class="fas fa-lock-open"></i> Abrir caja</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Cada turno de caja es una sesión: apertura, movimientos y
                        cierre con arqueo.</small><br>
                    <small><i class="fas fa-robot me-1"></i>Las cajas que queden abiertas se cierran solas según la
                        automatización configurada.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">
                    @include('ventas.partials.alertas')

                    <form method="GET" action="{{ route('caja.sesiones.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <select name="caja_id" class="form-select form-select-sm">
                                <option value="">Todas las cajas</option>
                                @foreach ($cajas as $id => $nombre)
                                    <option value="{{ $id }}" @selected((string) request('caja_id') === (string) $id)>{{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                <option value="abierta" @selected(request('estado') === 'abierta')>Abiertas</option>
                                <option value="cerrada" @selected(request('estado') === 'cerrada')>Cerradas</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="date" name="desde" value="{{ request('desde') }}"
                                class="form-control form-control-sm" title="Desde"></div>
                        <div class="col-md-2"><input type="date" name="hasta" value="{{ request('hasta') }}"
                                class="form-control form-control-sm" title="Hasta"></div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('caja.sesiones.index') }}"
                                class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tienda / Caja</th>
                                    <th>Cajero</th>
                                    <th>Apertura</th>
                                    <th>Cierre</th>
                                    <th class="text-end">Inicial</th>
                                    <th class="text-end">Ingresos</th>
                                    <th class="text-end">Egresos</th>
                                    <th class="text-end">Diferencia</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sesiones as $s)
                                    <tr>
                                        <td>{{ $s->id }}</td>
                                        <td>{{ $s->caja->tienda->nombre ?? '' }} / {{ $s->caja->nombre }}</td>
                                        <td>{{ $s->usuarioApertura->nombre_completo ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $s->fecha_apertura->format('d/m/Y H:i') }}</td>
                                        <td class="text-nowrap">
                                            {{ $s->fecha_cierre?->format('d/m/Y H:i') ?? '—' }}
                                            @if ($s->tipo_cierre === 'automatico')
                                                <span class="badge bg-gradient-secondary">Auto</span>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($s->monto_apertura) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($s->total_ingresos) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($s->total_egresos) }}</td>
                                        <td class="text-end">
                                            {{ $s->estado === 'cerrada' ? \App\Support\Formato::dinero($s->diferencia) : '—' }}
                                        </td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', ['estado' => $s->estado])</td>
                                        <td class="text-end">
                                            <a href="{{ route('caja.sesiones.show', $s) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver detalle"><i
                                                    class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-3">No hay sesiones de caja.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $sesiones->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
