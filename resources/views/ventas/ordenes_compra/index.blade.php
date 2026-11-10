@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Órdenes de compra</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('ordenes_compra.crear')
                        <a href="{{ route('ordenes_compra.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>
                            Nueva orden</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Una orden de compra no mueve stock: al recibir la
                        mercadería se genera la compra.</small><br>
                    <small><i class="fas fa-boxes me-1"></i>Puedes recibir una orden en varias entregas (estado
                        parcial).</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')


                    <form method="GET" action="{{ route('ordenes_compra.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Número o proveedor...">
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                                <option value="parcial" @selected(request('estado') === 'parcial')>Parciales</option>
                                <option value="recibida" @selected(request('estado') === 'recibida')>Recibidas</option>
                                <option value="anulada" @selected(request('estado') === 'anulada')>Anuladas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="desde" value="{{ request('desde') }}"
                                class="form-control form-control-sm" title="Desde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="hasta" value="{{ request('hasta') }}"
                                class="form-control form-control-sm" title="Hasta">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('ordenes_compra.index') }}"
                                class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Tienda</th>
                                    <th>Entrega estimada</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ordenes as $orden)
                                    <tr>
                                        <td>{{ $orden->numero }}</td>
                                        <td>{{ $orden->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $orden->proveedor->nombre ?? '—' }}</td>
                                        <td>{{ $orden->tienda->nombre ?? '' }}</td>
                                        <td>{{ $orden->fecha_entrega_estimada?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($orden->total) }}</td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', [
                                            'estado' => $orden->estado,
                                        ])</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('ordenes_compra.show', $orden) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver"><i
                                                    class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-3">No se encontraron registros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $ordenes->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
