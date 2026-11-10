@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Transferencias</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('transferencias.crear')
                        <a href="{{ route('transferencias.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>
                            Nueva transferencia</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Traslada stock entre almacenes de una tienda o entre
                        sucursales.</small><br>
                    <small><i class="fas fa-route me-1"></i>Flujo: pendiente → enviada (sale del origen) → recibida (entra
                        al destino).</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')


                    <form method="GET" action="{{ route('transferencias.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Número...">
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                                <option value="en_transito" @selected(request('estado') === 'en_transito')>En tránsito</option>
                                <option value="recibida" @selected(request('estado') === 'recibida')>Recibidas</option>
                                <option value="anulada" @selected(request('estado') === 'anulada')>Anuladas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="tipo" class="form-select form-select-sm">
                                <option value="">Todos los tipos</option>
                                <option value="entre_almacenes" @selected(request('tipo') === 'entre_almacenes')>Entre almacenes</option>
                                <option value="entre_sucursales" @selected(request('tipo') === 'entre_sucursales')>Entre sucursales</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('transferencias.index') }}"
                                class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Origen</th>
                                    <th>Destino</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transferencias as $t)
                                    <tr>
                                        <td>{{ $t->numero }}</td>
                                        <td>{{ $t->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $t->tipo === 'entre_almacenes' ? 'Entre almacenes' : 'Entre sucursales' }}
                                        </td>
                                        <td>{{ $t->tiendaOrigen->nombre ?? '' }} / {{ $t->almacenOrigen->nombre ?? '' }}
                                        </td>
                                        <td>{{ $t->tiendaDestino->nombre ?? '' }} / {{ $t->almacenDestino->nombre ?? '' }}
                                        </td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', ['estado' => $t->estado])</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('transferencias.show', $t) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver"><i
                                                    class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No se encontraron registros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $transferencias->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
