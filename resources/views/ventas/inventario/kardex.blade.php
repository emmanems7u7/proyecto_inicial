@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Kardex de inventario</h5>
                @if ($producto)
                    <p class="mb-2"><strong>{{ $producto->codigo }}</strong> - {{ $producto->nombre }}</p>
                @endif

                <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('inventario.movimientos')
                    <a href="{{ route('inventario.movimiento.create', array_filter(['almacen_id' => request('almacen_id'), 'producto_id' => request('producto_id')])) }}" class="btn btn-sm btn-primary"><i class="fas fa-exchange-alt"></i> Registrar movimiento</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>Historial de entradas y salidas con saldo antes y después de cada movimiento.</small><br>
                <small><i class="fas fa-lock me-1"></i>El kardex no se edita: los errores se corrigen con un movimiento de ajuste.</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('inventario.kardex') }}" class="row g-2 mb-3">
                    @if ($producto)
                        <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                    @endif
                    <div class="col-md-3">
                        <select name="almacen_id" class="form-select form-select-sm">
                            <option value="">Todos los almacenes</option>
                            @foreach ($opcionesAlmacen as $id => $nombre)
                                <option value="{{ $id }}" @selected((string) request('almacen_id') === (string) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Producto...">
                    </div>
                    <div class="col-md-2">
                        <select name="tipo" class="form-select form-select-sm">
                            <option value="">Todos los tipos</option>
                            @foreach ($tipos as $clave => $nombre)
                                <option value="{{ $clave }}" @selected(request('tipo') === $clave)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm" title="Desde">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm" title="Hasta">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('inventario.kardex') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Almacén</th>
                                <th>Producto</th>
                                <th>Lote</th>
                                <th>Tipo</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Saldo ant.</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-end">Costo</th>
                                <th>Motivo / Obs.</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movimientos as $m)
                                <tr>
                                    <td class="text-nowrap">{{ $m->fecha->format('d/m/Y H:i') }}</td>
                                    <td>{{ $m->almacen->nombre_completo ?? '—' }}</td>
                                    <td>{{ $m->producto->codigo ?? '' }} - {{ $m->producto->nombre ?? '—' }}</td>
                                    <td>{{ $m->lote->numero_lote ?? '—' }}</td>
                                    <td><span class="badge bg-gradient-{{ $m->cantidad >= 0 ? 'success' : 'danger' }}">{{ $m->tipo_nombre }}</span></td>
                                    <td class="text-end {{ $m->cantidad >= 0 ? 'text-success' : 'text-danger' }}">
                                        <strong>{{ $m->cantidad >= 0 ? '+' : '' }}{{ \App\Support\Formato::cantidad($m->cantidad) }}</strong>
                                    </td>
                                    <td class="text-end">{{ \App\Support\Formato::cantidad($m->saldo_anterior) }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::cantidad($m->saldo_posterior) }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($m->costo_unitario) }}</td>
                                    <td>{{ $m->motivo->catalogo_descripcion ?? '' }}@if ($m->motivo && $m->observacion) · @endif{{ $m->observacion }}</td>
                                    <td>{{ $m->user->nombre_completo ?? 'Sistema' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center text-muted py-3">No hay movimientos con esos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $movimientos->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
