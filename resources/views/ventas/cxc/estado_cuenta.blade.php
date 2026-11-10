@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Estado de cuenta - {{ $cliente->nombre }}</h5>
                <small>{{ $cliente->numero_documento }} · {{ $cliente->telefono }} · {{ $cliente->email }}</small>

                <form method="GET" class="row g-2 my-2">
                    <div class="col-md-3">
                        <select name="tienda_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todas las tiendas</option>
                            @foreach ($tiendas as $id => $nombre)
                                <option value="{{ $id }}" @selected((string) request('tienda_id') === (string) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('cxc.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                        <button type="button" class="btn btn-sm btn-info" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm small-table">
                        <thead><tr><th>Documento</th><th>Tienda</th><th>Emisión</th><th>Vence</th><th class="text-end">Total</th><th class="text-end">Pagado</th><th class="text-end">Saldo</th><th class="text-center">Estado</th></tr></thead>
                        <tbody>
                            @forelse ($cuentas as $c)
                                <tr>
                                    <td><a href="{{ route('cxc.show', $c) }}">{{ $c->numero_documento }}</a></td>
                                    <td>{{ $c->tienda->nombre ?? '' }}</td>
                                    <td>{{ $c->fecha_emision->format('d/m/Y') }}</td>
                                    <td>{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($c->monto_total) }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($c->monto_pagado) }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($c->saldo) }}</td>
                                    <td class="text-center">@include('ventas.partials.badge_estado', ['estado' => $c->estado_efectivo])</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-3">Sin documentos.</td></tr>
                            @endforelse
                        </tbody>
                        @if ($cuentas->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Totales</strong></td>
                                    <td class="text-end"><strong>{{ \App\Support\Formato::dinero($cuentas->sum('monto_total')) }}</strong></td>
                                    <td class="text-end"><strong>{{ \App\Support\Formato::dinero($cuentas->sum('monto_pagado')) }}</strong></td>
                                    <td class="text-end"><strong>{{ \App\Support\Formato::dinero($cuentas->sum('saldo')) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
