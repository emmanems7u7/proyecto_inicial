@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

@php
    $puedeDevolver = $venta->estado === 'completada' && $pendientes->sum('disponible') > 0;
@endphp

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Venta {{ $venta->numero }} @include('ventas.partials.badge_estado', ['estado' => $venta->estado])</h5>
                <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                <a href="{{ route('ventas.imprimir', $venta) }}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-print"></i> Imprimir / PDF</a>
                @can('ventas.devolver')
                    @if ($puedeDevolver)
                        <a href="{{ route('ventas.devolucion.create', $venta) }}" class="btn btn-sm btn-warning"><i class="fas fa-undo"></i> Devolución</a>
                    @endif
                @endcan
                @if ($venta->cuentaPorCobrar)
                    @can('cxc.ver')
                        <a href="{{ route('cxc.show', $venta->cuentaPorCobrar) }}" class="btn btn-sm btn-primary"><i class="fas fa-hand-holding-usd"></i> Cuenta por cobrar</a>
                    @endcan
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <small>Tienda / almacén: <strong>{{ $venta->tienda->nombre }} / {{ $venta->almacen->nombre }}</strong></small><br>
            <small>Fecha: <strong>{{ $venta->fecha->format('d/m/Y H:i') }}</strong></small><br>
            <small>Cliente: <strong>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</strong></small><br>
            <small>Vendedor: <strong>{{ $venta->vendedor->nombre_completo ?? '—' }}</strong> · Pago: <strong>{{ ucfirst($venta->tipo_pago) }}</strong></small>
            @if ($venta->observaciones) <br><small>Obs.: {{ $venta->observaciones }}</small> @endif
            @if ($venta->estado === 'anulada') <br><small class="text-danger">Anulada {{ $venta->anulada_at?->format('d/m/Y H:i') }}: {{ $venta->motivo_anulacion }}</small> @endif
        </div></div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            <div class="table-responsive">
                <table class="table table-sm small-table">
                    <thead><tr><th>Producto</th><th>Lote</th><th class="text-end">Cant.</th><th class="text-end">Precio</th><th class="text-end">Desc.</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($venta->detalles as $d)
                            <tr>
                                <td>{{ $d->producto->codigo }} - {{ $d->producto->nombre }}</td>
                                <td>{{ $d->lote->numero_lote ?? '—' }}</td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($d->cantidad) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->precio_unitario) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->descuento) }}</td>
                                <td class="text-end">{{ \App\Support\Formato::dinero($d->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="5" class="text-end">Subtotal</td><td class="text-end">{{ \App\Support\Formato::dinero($venta->subtotal) }}</td></tr>
                        <tr><td colspan="5" class="text-end">Descuento global</td><td class="text-end">{{ \App\Support\Formato::dinero($venta->descuento) }}</td></tr>
                        <tr><td colspan="5" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>{{ \App\Support\Formato::dinero($venta->total) }}</strong></td></tr>
                        <tr><td colspan="5" class="text-end">Cobrado</td><td class="text-end">{{ \App\Support\Formato::dinero($venta->pagado) }}</td></tr>
                        <tr><td colspan="5" class="text-end">Saldo</td><td class="text-end">{{ \App\Support\Formato::dinero($venta->saldo) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div></div>

        @if ($venta->devoluciones->isNotEmpty())
            <div class="card mt-2"><div class="card-body">
                <h6>Devoluciones</h6>
                <table class="table table-sm small-table">
                    <thead><tr><th>Número</th><th>Fecha</th><th>Motivo</th><th>Usuario</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        @foreach ($venta->devoluciones as $dv)
                            <tr><td>{{ $dv->numero }}</td><td>{{ $dv->fecha->format('d/m/Y') }}</td><td>{{ $dv->motivo ?: '—' }}</td><td>{{ $dv->user->nombre_completo ?? '—' }}</td><td class="text-end">{{ \App\Support\Formato::dinero($dv->total) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        @endif
    </div>

    <div class="col-lg-4">
        @if ($movimientos->isNotEmpty() || ($venta->cuentaPorCobrar && $venta->cuentaPorCobrar->pagos->isNotEmpty()))
            <div class="card"><div class="card-body">
                <h6>Cobros</h6>
                <table class="table table-sm small-table">
                    <tbody>
                        @foreach ($movimientos as $m)
                            <tr><td>{{ $m->fecha->format('d/m H:i') }}</td><td>{{ $m->concepto }}<br><small class="text-muted">{{ $m->metodoPago->catalogo_descripcion ?? '' }}</small></td><td class="text-end">{{ $m->tipo === 'egreso' ? '-' : '' }}{{ \App\Support\Formato::dinero($m->monto) }}</td></tr>
                        @endforeach
                        @if ($venta->cuentaPorCobrar)
                            @foreach ($venta->cuentaPorCobrar->pagos as $p)
                                <tr><td>{{ $p->fecha->format('d/m H:i') }}</td><td>Pago a cuenta<br><small class="text-muted">{{ $p->metodoPago->catalogo_descripcion ?? '' }}</small></td><td class="text-end">{{ \App\Support\Formato::dinero($p->monto) }}</td></tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div></div>
        @endif

        @if ($venta->estado === 'pedido')
            @can('ventas.crear')
                <div class="card mt-2"><div class="card-body">
                    <h6>Confirmar pedido</h6>
                    <form method="POST" action="{{ route('ventas.confirmar', $venta) }}">
                        @csrf
                        <div class="row">
                            @include('ventas.partials.campo_select', ['name' => 'tipo_pago', 'label' => 'Tipo de pago', 'col' => 12, 'sinVacio' => true, 'value' => $venta->tipo_pago, 'options' => ['contado' => 'Contado', 'credito' => 'Crédito']])
                            @include('ventas.partials.campo_select', ['name' => 'metodo_pago_id', 'label' => 'Método de pago', 'col' => 12, 'value' => $efectivoId, 'options' => $metodos])
                            @include('ventas.partials.campo_texto', ['name' => 'anticipo', 'label' => 'Anticipo (solo crédito)', 'col' => 12, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => '0'])
                        </div>
                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Confirmar y descontar stock</button>
                    </form>
                </div></div>
            @endcan
        @endif

        @if (in_array($venta->estado, ['completada', 'pedido']))
            @can('ventas.anular')
                <div class="card mt-2"><div class="card-body">
                    <h6>Anular</h6>
                    <form method="POST" action="{{ route('ventas.anular', $venta) }}" onsubmit="return confirm('¿Anular esta {{ $venta->estado === 'pedido' ? 'orden' : 'venta' }}? Se restituirá el stock.');">
                        @csrf
                        <div class="row">
                            @include('ventas.partials.campo_texto', ['name' => 'motivo', 'label' => 'Motivo', 'col' => 12, 'required' => true, 'maxlength' => 255])
                        </div>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-ban"></i> Anular</button>
                    </form>
                </div></div>
            @endcan
        @endif
    </div>
</div>

@endsection
