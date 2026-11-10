@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Orden de compra {{ $orden->numero }} @include('ventas.partials.badge_estado', ['estado' => $orden->estado])</h5>
                <a href="{{ route('ordenes_compra.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @if (in_array($orden->estado, ['pendiente', 'parcial']))
                    @can('compras.crear')
                        <a href="{{ route('compras.create', ['orden_compra_id' => $orden->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-truck-loading"></i> Recibir mercadería</a>
                    @endcan
                @endif
                @if ($orden->estado === 'pendiente')
                    @can('ordenes_compra.anular')
                        <form method="POST" action="{{ route('ordenes_compra.anular', $orden) }}" class="d-inline" onsubmit="return confirm('¿Anular esta orden?');">
                            @csrf
                            <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Anular</button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <small>Proveedor: <strong>{{ $orden->proveedor->nombre }}</strong></small><br>
            <small>Destino: <strong>{{ $orden->tienda->nombre }} / {{ $orden->almacen->nombre }}</strong></small><br>
            <small>Fecha: <strong>{{ $orden->fecha->format('d/m/Y') }}</strong> · Entrega estimada: <strong>{{ $orden->fecha_entrega_estimada?->format('d/m/Y') ?? '—' }}</strong></small><br>
            <small>Creada por: <strong>{{ $orden->user->nombre_completo ?? '—' }}</strong></small>
            @if ($orden->observaciones) <br><small>Obs.: {{ $orden->observaciones }}</small> @endif
        </div></div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-12">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            <div class="table-responsive">
                <table class="table table-sm small-table">
                    <thead><tr><th>Producto</th><th class="text-end">Pedido</th><th class="text-end">Recibido</th><th class="text-end">Pendiente</th><th class="text-end">Costo</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($orden->detalles as $d)
                            <tr>
                                <td>{{ $d->producto->codigo }} - {{ $d->producto->nombre }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad_recibida) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad(max(0, $d->cantidad - $d->cantidad_recibida)) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->costo_unitario) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="5" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>{{ \App\Support\Formato::dinero($orden->total) }}</strong></td></tr></tfoot>
                </table>
            </div>
        </div></div>
    </div>
</div>

@endsection
