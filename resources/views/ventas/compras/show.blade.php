@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Compra {{ $compra->numero }} @include('ventas.partials.badge_estado', ['estado' => $compra->estado])</h5>
                <a href="{{ route('compras.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @if ($compra->estado === 'recibida')
                    @can('compras.devolver')
                        <a href="{{ route('compras.devolucion.create', $compra) }}" class="btn btn-sm btn-warning"><i class="fas fa-undo"></i> Devolución</a>
                    @endcan
                    @can('compras.anular')
                        <form method="POST" action="{{ route('compras.anular', $compra) }}" class="d-inline" onsubmit="return confirm('¿Anular la compra? Se descontará el stock ingresado.');">
                            @csrf
                            <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Anular</button>
                        </form>
                    @endcan
                @endif
                @if ($compra->cuentaPorPagar)
                    @can('cxp.ver')
                        <a href="{{ route('cxp.show', $compra->cuentaPorPagar) }}" class="btn btn-sm btn-primary"><i class="fas fa-hand-holding-usd"></i> Cuenta por pagar</a>
                    @endcan
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <small>Proveedor: <strong>{{ $compra->proveedor->nombre }}</strong></small><br>
            <small>Destino: <strong>{{ $compra->tienda->nombre }} / {{ $compra->almacen->nombre }}</strong></small><br>
            <small>Fecha: <strong>{{ $compra->fecha->format('d/m/Y H:i') }}</strong> · Pago: <strong>{{ ucfirst($compra->tipo_pago) }}</strong></small><br>
            <small>Doc. proveedor: <strong>{{ $compra->numero_documento_proveedor ?: '—' }}</strong></small><br>
            @if ($compra->ordenCompra) <small>Orden: <a href="{{ route('ordenes_compra.show', $compra->ordenCompra) }}">{{ $compra->ordenCompra->numero }}</a></small><br> @endif
            <small>Registró: <strong>{{ $compra->user->nombre_completo ?? '—' }}</strong></small>
        </div></div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-12">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            <div class="table-responsive">
                <table class="table table-sm small-table">
                    <thead><tr><th>Producto</th><th>Lote</th><th>Vence</th><th class="text-end">Cant.</th><th class="text-end">Costo</th><th class="text-end">Desc.</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($compra->detalles as $d)
                            <tr>
                                <td>{{ $d->producto->codigo }} - {{ $d->producto->nombre }}@if ($d->presentacion_nombre)<br><small class="text-muted">{{ $d->presentacion_nombre }} × {{ \App\Support\Formato::cantidad($d->cantidad / max((float) $d->factor, 1)) }}</small>@endif</td>
                                <td>{{ $d->numero_lote ?: '—' }}</td>
                                <td>{{ $d->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->costo_unitario) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->descuento) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="6" class="text-end">Subtotal</td><td class="text-end">{{ \App\Support\Formato::dinero($compra->subtotal) }}</td></tr>
                        <tr><td colspan="6" class="text-end">Descuento global</td><td class="text-end">{{ \App\Support\Formato::dinero($compra->descuento) }}</td></tr>
                        <tr><td colspan="6" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>{{ \App\Support\Formato::dinero($compra->total) }}</strong></td></tr>
                        <tr><td colspan="6" class="text-end">Pagado</td><td class="text-end">{{ \App\Support\Formato::dinero($compra->pagado) }}</td></tr>
                        <tr><td colspan="6" class="text-end">Saldo</td><td class="text-end">{{ \App\Support\Formato::dinero($compra->saldo) }}</td></tr>
                    </tfoot>
                </table>
            </div>
            @if ($compra->devoluciones->isNotEmpty())
                <h6 class="mt-3">Devoluciones</h6>
                <table class="table table-sm small-table">
                    <thead><tr><th>Número</th><th>Fecha</th><th>Motivo</th><th>Usuario</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach ($compra->devoluciones as $dv)
                            <tr><td>{{ $dv->numero }}</td><td>{{ $dv->fecha->format('d/m/Y') }}</td><td>{{ $dv->motivo ?: '—' }}</td><td>{{ $dv->user->nombre_completo ?? '—' }}</td><td class="text-end">{{ \App\Support\Formato::dinero($dv->total) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div></div>
    </div>
</div>

@endsection
