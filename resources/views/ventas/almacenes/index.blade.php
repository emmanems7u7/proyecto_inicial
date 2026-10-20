@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Administración de Almacenes</h5>

                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('almacenes.crear')
                    <a href="{{ route('almacenes.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nuevo almacén</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>Cada almacén pertenece a una tienda y maneja su propio stock.</small><br>
                <small><i class="fas fa-star me-1"></i>Marca un almacén como principal para que sea el predeterminado de la tienda.</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('almacenes.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar por nombre o código...">
                    </div>
                    <div class="col-md-3">
                        <select name="tienda_id" class="form-select form-select-sm">
                            <option value="">Todas las tiendas</option>
                            @foreach ($tiendas as $id => $nombre)
                                <option value="{{ $id }}" @selected((string) request('tienda_id') === (string) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('almacenes.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Tienda</th>
                                <th>Código</th>
                                <th>Almacén</th>
                                <th>Productos con registro</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($almacenes as $almacen)
                                <tr>
                                    <td>{{ $almacen->tienda->nombre ?? '—' }}</td>
                                    <td>{{ $almacen->codigo }}</td>
                                    <td>{{ $almacen->nombre }} @if ($almacen->es_principal)<span class="badge bg-gradient-info ms-1">Principal</span>@endif</td>
                                    <td class="text-center">{{ $almacen->inventarios_count }}</td>
                                    <td class="text-center"><span class="badge bg-gradient-{{ $almacen->estado ? 'success' : 'secondary' }}">{{ $almacen->estado ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="text-end text-nowrap">

                                        @can('almacenes.editar')
                                            <a href="{{ route('almacenes.edit', $almacen) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('almacenes.eliminar')
                                            <form action="{{ route('almacenes.destroy', $almacen) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No se encontraron registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $almacenes->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
