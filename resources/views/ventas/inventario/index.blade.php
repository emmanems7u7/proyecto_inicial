@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Inventario - Stock por almacén</h5>

                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('inventario.movimientos')
                    <a href="{{ route('inventario.movimiento.create', ['almacen_id' => $almacenId]) }}" class="btn btn-sm btn-primary"><i class="fas fa-exchange-alt"></i> Registrar movimiento</a>
                @endcan
                @can('kardex.ver')
                    <a href="{{ route('inventario.kardex', ['almacen_id' => $almacenId]) }}" class="btn btn-sm btn-info"><i class="fas fa-list"></i> Kardex</a>
                @endcan
                @can('lotes.ver')
                    <a href="{{ route('inventario.lotes', ['almacen_id' => $almacenId]) }}" class="btn btn-sm btn-warning"><i class="fas fa-calendar-alt"></i> Lotes</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>El stock es independiente por almacén. Todo cambio de stock se registra en el kardex.</small><br>
                <small><i class="fas fa-bell me-1"></i>Define stock mínimo y máximo por producto para recibir alertas de reposición.</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                @if ($opcionesAlmacen->isEmpty())
                    <div class="alert alert-warning text-white py-2">No tienes almacenes disponibles. Crea uno en Almacenes o pide acceso a una tienda.</div>
                @else
                <form method="GET" action="{{ route('inventario.index') }}" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="almacen_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach ($opcionesAlmacen as $id => $nombre)
                                <option value="{{ $id }}" @selected($id === $almacenId)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar producto...">
                    </div>
                    <div class="col-md-2">
                        <select name="estado_stock" class="form-select form-select-sm">
                            <option value="">Todo el stock</option>
                            <option value="con_stock" @selected($estadoStock === 'con_stock')>Con stock</option>
                            <option value="sin_stock" @selected($estadoStock === 'sin_stock')>Sin stock</option>
                            <option value="bajo_minimo" @selected($estadoStock === 'bajo_minimo')>Bajo mínimo</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('inventario.index', ['almacen_id' => $almacenId]) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Unidad</th>
                                <th class="text-end">Stock</th>
                                <th class="text-end">Mínimo</th>
                                <th class="text-end">Máximo</th>
                                <th>Ubicación</th>
                                <th class="text-center">Situación</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productos as $producto)
                                @php
                                    $inv = $producto->inventarios->first();
                                    $cant = (float) ($inv->cantidad ?? 0);
                                    $min = (float) ($inv->stock_minimo ?? 0);
                                @endphp
                                <tr>
                                    <td>{{ $producto->codigo }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>{{ $producto->unidad->catalogo_descripcion ?? '—' }}</td>
                                    <td class="text-end"><strong>{{ \App\Support\Formato::cantidad($cant) }}</strong></td>
                                    <td class="text-end">{{ \App\Support\Formato::cantidad($min) }}</td>
                                    <td class="text-end">{{ $inv && $inv->stock_maximo !== null ? \App\Support\Formato::cantidad($inv->stock_maximo) : '—' }}</td>
                                    <td>{{ $inv->ubicacion ?? '—' }}</td>
                                    <td class="text-center">
                                        @if ($cant <= 0)
                                            <span class="badge bg-gradient-secondary">Sin stock</span>
                                        @elseif ($min > 0 && $cant <= $min)
                                            <span class="badge bg-gradient-danger">Bajo mínimo</span>
                                        @else
                                            <span class="badge bg-gradient-success">Normal</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @can('inventario.movimientos')
                                            <a href="{{ route('inventario.movimiento.create', ['almacen_id' => $almacenId, 'producto_id' => $producto->id]) }}" class="btn btn-xs btn-primary mb-0 px-2 py-1" title="Registrar movimiento"><i class="fas fa-exchange-alt"></i></a>
                                        @endcan
                                        @can('kardex.ver')
                                            <a href="{{ route('inventario.kardex', ['almacen_id' => $almacenId, 'producto_id' => $producto->id]) }}" class="btn btn-xs btn-info mb-0 px-2 py-1" title="Kardex"><i class="fas fa-list"></i></a>
                                        @endcan
                                        @can('inventario.configurar')
                                            <a href="{{ route('inventario.config.edit', ['almacen_id' => $almacenId, 'producto_id' => $producto->id]) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Stock mínimo / máximo"><i class="fas fa-sliders-h"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No se encontraron productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $productos->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
