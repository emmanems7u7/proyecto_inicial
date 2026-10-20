@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Catálogo de Productos</h5>

                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('productos.crear')
                    <a href="{{ route('productos.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nuevo producto</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>El producto es global para toda la empresa; el stock se controla por almacén.</small><br>
                <small><i class="fas fa-layer-group me-1"></i>Activa lotes y vencimiento solo en productos perecibles o con trazabilidad.</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('productos.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar por nombre, código o código de barras...">
                    </div>
                    <div class="col-md-3">
                        <select name="categoria_producto_id" class="form-select form-select-sm">
                            <option value="">Todas las categorías</option>
                            @foreach ($categorias as $id => $nombre)
                                <option value="{{ $id }}" @selected((string) request('categoria_producto_id') === (string) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <option value="1" @selected(request('estado') === '1')>Activos</option>
                            <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Unidad</th>
                                <th>P. venta</th>
                                <th>Stock total</th>
                                <th>Control</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productos as $producto)
                                <tr>
                                    <td>{{ $producto->codigo }}</td>
                                    <td>{{ $producto->nombre }}@if ($producto->codigo_barras)<br><small class="text-muted">{{ $producto->codigo_barras }}</small>@endif</td>
                                    <td>{{ $producto->categoria->nombre ?? '—' }}</td>
                                    <td>{{ $producto->unidad->catalogo_descripcion ?? '—' }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($producto->precio_venta) }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::cantidad($producto->stock_total ?? 0) }}</td>
                                    <td>@if ($producto->maneja_lotes)<span class="badge bg-gradient-info">Lotes</span>@endif @if ($producto->maneja_vencimiento)<span class="badge bg-gradient-warning">Vence</span>@endif</td>
                                    <td class="text-center"><span class="badge bg-gradient-{{ $producto->estado ? 'success' : 'secondary' }}">{{ $producto->estado ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="text-end text-nowrap">

                                        @can('productos.editar')
                                            <a href="{{ route('productos.edit', $producto) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('productos.eliminar')
                                            <form action="{{ route('productos.destroy', $producto) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No se encontraron registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $productos->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
