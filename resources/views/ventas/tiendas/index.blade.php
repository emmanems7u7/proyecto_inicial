@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Administración de Tiendas / Sucursales</h5>

                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('tiendas.crear')
                    <a href="{{ route('tiendas.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nueva tienda</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>Cada tienda tiene su propio almacén, stock, caja, vendedores y reportes.</small><br>
                <small><i class="fas fa-users me-1"></i>Asigna usuarios a cada tienda con el botón de usuarios; administradores generales ven todas.</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('tiendas.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar por nombre o código...">
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('tiendas.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Ciudad</th>
                                <th>Teléfono</th>
                                <th>Almacenes</th>
                                <th>Usuarios</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tiendas as $tienda)
                                <tr>
                                    <td>{{ $tienda->codigo }}</td>
                                    <td>{{ $tienda->nombre }} @if ($tienda->es_matriz)<span class="badge bg-gradient-info ms-1">Matriz</span>@endif</td>
                                    <td>{{ $tienda->ciudad->catalogo_descripcion ?? '—' }}</td>
                                    <td>{{ $tienda->telefono ?: '—' }}</td>
                                    <td class="text-center">{{ $tienda->almacenes_count }}</td>
                                    <td class="text-center">{{ $tienda->usuarios_count }}</td>
                                    <td class="text-center"><span class="badge bg-gradient-{{ $tienda->estado ? 'success' : 'secondary' }}">{{ $tienda->estado ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="text-end text-nowrap">
                                        @can('tiendas.usuarios')
                                            <a href="{{ route('tiendas.usuarios.edit', $tienda) }}" class="btn btn-xs btn-info mb-0 px-2 py-1" title="Usuarios de la tienda"><i class="fas fa-users"></i></a>
                                        @endcan
                                        @can('tiendas.editar')
                                            <a href="{{ route('tiendas.edit', $tienda) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('tiendas.eliminar')
                                            <form action="{{ route('tiendas.destroy', $tienda) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-3">No se encontraron registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $tiendas->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
