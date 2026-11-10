@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Cotizaciones</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('cotizaciones.crear')
                        <a href="{{ route('cotizaciones.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i>
                            Nueva cotización</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Las cotizaciones no mueven stock ni caja. Puedes
                        convertirlas en venta con un clic.</small><br>
                    <small><i class="fas fa-print me-1"></i>Imprime o guarda en PDF la cotización para enviarla al
                        cliente.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')


                    <form method="GET" action="{{ route('cotizaciones.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Buscar por número o cliente...">
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendientes</option>
                                <option value="aceptada" @selected(request('estado') === 'aceptada')>Aceptadas</option>
                                <option value="rechazada" @selected(request('estado') === 'rechazada')>Rechazadas</option>
                                <option value="vencida" @selected(request('estado') === 'vencida')>Vencidas</option>
                                <option value="convertida" @selected(request('estado') === 'convertida')>Convertidas</option>
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
                            <a href="{{ route('cotizaciones.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cotizaciones as $cotizacion)
                                    <tr>
                                        <td>{{ $cotizacion->numero }}</td>
                                        <td>{{ $cotizacion->fecha->format('d/m/Y') }}</td>
                                        <td>{{ $cotizacion->cliente->nombre ?? 'Sin cliente' }}</td>
                                        <td>{{ $cotizacion->vendedor->nombre_completo ?? '—' }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($cotizacion->total) }}</td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', [
                                            'estado' => $cotizacion->estado,
                                        ])</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('cotizaciones.show', $cotizacion) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver"><i
                                                    class="fas fa-eye"></i></a>
                                            @if ($cotizacion->estado === 'pendiente')
                                                @can('cotizaciones.editar')
                                                    <a href="{{ route('cotizaciones.edit', $cotizacion) }}"
                                                        class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i
                                                            class="fas fa-edit"></i></a>
                                                @endcan
                                            @endif
                                            @if ($cotizacion->estado !== 'convertida')
                                                @can('cotizaciones.eliminar')
                                                    <form action="{{ route('cotizaciones.destroy', $cotizacion) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('¿Eliminar esta cotización?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i
                                                                class="fas fa-trash"></i></button>
                                                    </form>
                                                @endcan
                                            @endif
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
                        {{ $cotizaciones->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
