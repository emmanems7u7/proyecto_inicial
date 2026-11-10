@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Configuración de Cajas</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('cajas.crear')
                        <a href="{{ route('cajas.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nueva
                            caja</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Una caja es un punto de cobro de la tienda. Cada turno se
                        registra como una sesión de caja.</small><br>
                    <small><i class="fas fa-lock me-1"></i>Una caja solo puede tener una sesión abierta a la
                        vez.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')

                    <form method="GET" action="{{ route('cajas.index') }}" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Buscar por nombre o código...">
                        </div>
                        <div class="col-md-2">
                            <select name="tienda_id" class="form-select form-select-sm">
                                <option value="">Todas las tiendas</option>
                                @foreach ($tiendas as $id => $nombre)
                                    <option value="{{ $id }}" @selected((string) request('tienda_id') === (string) $id)>{{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('cajas.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Tienda</th>
                                    <th>Código</th>
                                    <th>Caja</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cajas as $caja)
                                    <tr>
                                        <td>{{ $caja->tienda->nombre ?? '—' }}</td>
                                        <td>{{ $caja->codigo }}</td>
                                        <td>{{ $caja->nombre }}</td>
                                        <td class="text-center"><span
                                                class="badge bg-gradient-{{ $caja->estado ? 'success' : 'secondary' }}">{{ $caja->estado ? 'Activo' : 'Inactivo' }}</span>
                                        </td>
                                        <td class="text-end text-nowrap">

                                            @can('cajas.editar')
                                                <a href="{{ route('cajas.edit', $caja) }}"
                                                    class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i
                                                        class="fas fa-edit"></i></a>
                                            @endcan
                                            @can('cajas.eliminar')
                                                <form action="{{ route('cajas.destroy', $caja) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i
                                                            class="fas fa-trash"></i></button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No se encontraron registros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $cajas->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
