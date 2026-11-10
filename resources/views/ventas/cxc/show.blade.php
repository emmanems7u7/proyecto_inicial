@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Cuentas por cobrar - {{ $cuenta->numero_documento }} @include('ventas.partials.badge_estado', ['estado' => $cuenta->estado_efectivo])</h5>
                <a href="{{ route('cxc.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                <a href="{{ route('cxc.cliente', $cuenta->cliente) }}" class="btn btn-sm btn-info"><i class="fas fa-file-invoice"></i> Estado de cuenta</a>
                @if ($cuenta->venta)
                    <a href="{{ route('ventas.show', $cuenta->venta) }}" class="btn btn-sm btn-primary">Ver venta</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card"><div class="card-body">
            <small>Cliente: <strong>{{ $cuenta->cliente->nombre }}</strong></small><br>
            <small>Tienda: <strong>{{ $cuenta->tienda->nombre }}</strong></small><br>
            <small>Emisión: <strong>{{ $cuenta->fecha_emision->format('d/m/Y') }}</strong> · Vence: <strong>{{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</strong>
                @if ($cuenta->dias_vencido > 0 && $cuenta->saldo > 0) <span class="text-danger">({{ $cuenta->dias_vencido }} días vencido)</span> @endif</small>
        </div></div>
    </div>
</div>

<div class="row mt-2">
    @php $tarjetas = [['Total', $cuenta->monto_total, 'secondary'], ['Pagado', $cuenta->monto_pagado, 'success'], ['Saldo', $cuenta->saldo, $cuenta->saldo > 0 ? 'danger' : 'success']]; @endphp
    @foreach ($tarjetas as [$etq, $monto, $color])
        <div class="col-4 col-lg-2 mb-2"><div class="card"><div class="card-body p-3"><small class="text-muted">{{ $etq }}</small><h6 class="mb-0 text-{{ $color }}">Bs {{ \App\Support\Formato::dinero($monto) }}</h6></div></div></div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-7 mt-2">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            <h6>Historial de pagos</h6>
            <table class="table table-sm small-table">
                <thead><tr><th>Fecha</th><th>Método</th><th>Referencia</th><th>Usuario</th><th class="text-end">Monto</th></tr></thead>
                <tbody>
                    @forelse ($cuenta->pagos as $p)
                        <tr>
                            <td class="text-nowrap">{{ $p->fecha->format('d/m/Y H:i') }}</td>
                            <td>{{ $p->metodoPago->catalogo_descripcion ?? '—' }}</td>
                            <td>{{ $p->referencia ?: '—' }}@if ($p->observacion)<br><small class="text-muted">{{ $p->observacion }}</small>@endif</td>
                            <td>{{ $p->user->nombre_completo ?? '—' }}</td>
                            <td class="text-end">{{ \App\Support\Formato::dinero($p->monto) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Sin pagos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>

    <div class="col-lg-5 mt-2">
        @if (! in_array($cuenta->estado, ['pagada', 'anulada']) && $cuenta->saldo > 0)
            @can('cxc.cobrar')
                <div class="card"><div class="card-body">
                    <h6>Registrar cobro</h6>
                    @unless ($sesion)
                        <div class="alert alert-warning text-white py-2"><small>No tienes caja abierta: el cobro se registra en la cuenta pero no en una caja.</small></div>
                    @endunless
                    <form method="POST" action="{{ route('cxc.pago.store', $cuenta) }}" autocomplete="off">
                        @csrf
                        <div class="row">
                            @include('ventas.partials.campo_texto', ['name' => 'monto', 'label' => 'Monto (Bs)', 'col' => 12, 'type' => 'number', 'step' => '0.01', 'min' => '0.01', 'required' => true, 'value' => number_format($cuenta->saldo, 2, '.', '')])
                            @include('ventas.partials.campo_select', ['name' => 'metodo_pago_id', 'label' => 'Método de pago', 'col' => 12, 'required' => true, 'value' => $efectivoId, 'options' => $metodos])
                            @include('ventas.partials.campo_texto', ['name' => 'referencia', 'label' => 'Referencia (n.º de transferencia, recibo...)', 'col' => 12, 'maxlength' => 100])
                            @include('ventas.partials.campo_texto', ['name' => 'observacion', 'label' => 'Observación', 'col' => 12, 'maxlength' => 255])
                        </div>
                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Registrar cobro</button>
                    </form>
                </div></div>
            @endcan
        @endif
    </div>
</div>

@endsection
