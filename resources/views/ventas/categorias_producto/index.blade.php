@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Categorías de Producto</h5>

                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('categorias_producto.crear')
                    <a href="{{ route('categorias_producto.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nueva categoría</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>Agrupa tus productos en categorías y subcategorías para filtrarlos y reportarlos mejor.</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('categorias_producto.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar por nombre...">
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('categorias_producto.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Categoría padre</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categorias as $categoria)
                                <tr>
                                    <td>{{ $categoria->nombre }}</td>
                                    <td>{{ $categoria->padre->nombre ?? '—' }}</td>
                                    <td class="text-center">{{ $categoria->productos_count }}</td>
                                    <td class="text-center"><span class="badge bg-gradient-{{ $categoria->estado ? 'success' : 'secondary' }}">{{ $categoria->estado ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="text-end text-nowrap">

                                        @can('categorias_producto.editar')
                                            <a href="{{ route('categorias_producto.edit', $categoria) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('categorias_producto.eliminar')
                                            <form action="{{ route('categorias_producto.destroy', $categoria) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No se encontraron registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $categorias->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
