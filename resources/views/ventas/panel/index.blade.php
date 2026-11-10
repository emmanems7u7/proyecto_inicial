@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Panel de control @if ($tienda) <small class="text-muted">- {{ $tienda->nombre }}</small> @endif</h5>
                <div>
                    @can('ventas.crear') <a href="{{ route('ventas.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-cash-register"></i> Nueva venta</a> @endcan
                    @can('caja.abrir')
                        @if (empty($d['mi_sesion'])) <a href="{{ route('caja.abrir.create') }}" class="btn btn-sm btn-success"><i class="fas fa-lock-open"></i> Abrir caja</a> @endif
                    @endcan
                    @can('compras.crear') <a href="{{ route('compras.create') }}" class="btn btn-sm btn-info"><i class="fas fa-truck-loading"></i> Nueva compra</a> @endcan
                    @can('inventario.movimientos') <a href="{{ route('inventario.movimiento.create') }}" class="btn btn-sm btn-warning"><i class="fas fa-exchange-alt"></i> Movimiento de stock</a> @endcan
                    @can('transferencias.crear') <a href="{{ route('transferencias.create') }}" class="btn btn-sm btn-secondary"><i class="fas fa-dolly"></i> Transferencia</a> @endcan
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body">
            @include('ventas.partials.alertas')
            @if (array_key_exists('mi_sesion', $d))
                @if ($d['mi_sesion'])
                    <small><i class="fas fa-cash-register me-1 text-success"></i>Tu caja <strong>{{ $d['mi_sesion']->caja->nombre }}</strong> está abierta desde las {{ $d['mi_sesion']->fecha_apertura->format('H:i') }}.
                        <a href="{{ route('caja.sesiones.show', $d['mi_sesion']) }}">Ver</a></small>
                @else
                    <small><i class="fas fa-cash-register me-1 text-muted"></i>No tienes una caja abierta.</small>
                @endif
            @else
                <small><i class="fas fa-info-circle me-1"></i>Los bloques del panel dependen de tus permisos.</small>
            @endif
        </div></div>
    </div>
</div>

<div class="row mt-2">
    @if (isset($d['ventas_hoy']))
        @php
            $tarjetas = [
                ['Ventas de hoy', 'Bs ' . \App\Support\Formato::dinero($d['ventas_hoy']), 'success'],
                ['N.º de ventas hoy', $d['ventas_hoy_nro'], 'primary'],
                ['Ventas del mes', 'Bs ' . \App\Support\Formato::dinero($d['ventas_mes']), 'info'],
            ];
        @endphp
        @foreach ($tarjetas as [$etq, $valor, $color])
            <div class="col-6 col-lg-3 mb-2"><div class="card"><div class="card-body p-3"><small class="text-muted">{{ $etq }}</small><h5 class="mb-0 text-{{ $color }}">{{ $valor }}</h5></div></div></div>
        @endforeach
    @endif
    @if (isset($d['cxc_saldo']))
        <div class="col-6 col-lg-3 mb-2"><div class="card"><div class="card-body p-3">
            <small class="text-muted">Por cobrar</small><h5 class="mb-0">Bs {{ \App\Support\Formato::dinero($d['cxc_saldo']) }}</h5>
            <small class="text-danger">Vencido: Bs {{ \App\Support\Formato::dinero($d['cxc_vencido']) }} ({{ $d['cxc_vencidas_nro'] }})</small>
        </div></div></div>
    @endif
    @if (isset($d['cxp_saldo']))
        <div class="col-6 col-lg-3 mb-2"><div class="card"><div class="card-body p-3">
            <small class="text-muted">Por pagar</small><h5 class="mb-0">Bs {{ \App\Support\Formato::dinero($d['cxp_saldo']) }}</h5>
            <small class="text-danger">Vencido: Bs {{ \App\Support\Formato::dinero($d['cxp_vencido']) }} ({{ $d['cxp_vencidas_nro'] }})</small>
        </div></div></div>
    @endif
</div>

<div class="row">
    @if (isset($d['serie']))
        <div class="col-lg-6 mt-2">
            <div class="card"><div class="card-body">
                <h6>Ventas de los últimos 7 días</h6>
                @foreach ($d['serie'] as $p)
                    <div class="d-flex align-items-center mb-1">
                        <small style="width: 48px;">{{ $p['dia'] }}</small>
                        <div class="flex-grow-1 mx-2" style="background: #eef0f4; border-radius: 4px; height: 14px;">
                            <div style="width: {{ round($p['total'] / $d['serie_max'] * 100) }}%; background: #5e72e4; height: 14px; border-radius: 4px;"></div>
                        </div>
                        <small style="width: 90px; text-align: right;">{{ \App\Support\Formato::dinero($p['total']) }}</small>
                    </div>
                @endforeach
            </div></div>
        </div>
        <div class="col-lg-6 mt-2">
            <div class="card"><div class="card-body">
                <h6>Productos más vendidos del mes</h6>
                <table class="table table-sm small-table">
                    <thead><tr><th>Producto</th><th class="text-end">Cant.</th><th class="text-end">Bs</th></tr></thead>
                    <tbody>
                        @forelse ($d['top_productos'] as $tp)
                            <tr><td>{{ $tp->nombre }}</td><td class="text-end">{{ \App\Support\Formato::cantidad($tp->cant) }}</td><td class="text-end">{{ \App\Support\Formato::dinero($tp->total) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Sin ventas este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if (isset($d['bajo_minimo_nro']))
        <div class="col-lg-6 mt-2">
            <div class="card"><div class="card-body">
                <h6>Stock bajo el mínimo <span class="badge bg-gradient-{{ $d['bajo_minimo_nro'] ? 'danger' : 'success' }}">{{ $d['bajo_minimo_nro'] }}</span></h6>
                <table class="table table-sm small-table">
                    <tbody>
                        @forelse ($d['bajo_minimo'] as $i)
                            <tr><td>{{ $i->producto->nombre }}<br><small class="text-muted">{{ $i->almacen->nombre }}</small></td>
                                <td class="text-end">{{ \App\Support\Formato::cantidad($i->cantidad) }} / {{ \App\Support\Formato::cantidad($i->stock_minimo) }}</td></tr>
                        @empty
                            <tr><td class="text-center text-muted">Todo el stock está sobre el mínimo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @can('reportes.inventario') <a href="{{ route('reportes.inventario', ['tipo' => 'bajo_minimo']) }}"><small>Ver reporte completo</small></a> @endcan
            </div></div>
        </div>
        <div class="col-lg-6 mt-2">
            <div class="card"><div class="card-body">
                <h6>Lotes por vencer <span class="badge bg-gradient-{{ $d['lotes_nro'] ? 'warning' : 'success' }}">{{ $d['lotes_nro'] }}</span>
                    @if ($d['lotes_vencidos']) <span class="badge bg-gradient-danger">{{ $d['lotes_vencidos'] }} vencidos</span> @endif</h6>
                <table class="table table-sm small-table">
                    <tbody>
                        @forelse ($d['lotes'] as $l)
                            <tr><td>{{ $l->producto->nombre }}<br><small class="text-muted">Lote {{ $l->numero_lote }} · {{ $l->almacen->nombre }}</small></td>
                                <td class="text-end">{{ $l->fecha_vencimiento->format('d/m/Y') }}<br><small class="{{ $l->dias_para_vencer < 0 ? 'text-danger' : 'text-muted' }}">{{ $l->dias_para_vencer }} d</small></td></tr>
                        @empty
                            <tr><td class="text-center text-muted">Sin lotes próximos a vencer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @can('reportes.inventario') <a href="{{ route('reportes.inventario', ['tipo' => 'vencimientos']) }}"><small>Ver reporte completo</small></a> @endcan
            </div></div>
        </div>
    @endif

    @if (isset($d['cajas_abiertas']) && $d['cajas_abiertas']->isNotEmpty())
        <div class="col-lg-6 mt-2">
            <div class="card"><div class="card-body">
                <h6>Cajas abiertas ahora</h6>
                <table class="table table-sm small-table">
                    <tbody>
                        @foreach ($d['cajas_abiertas'] as $s)
                            <tr><td>{{ $s->caja->nombre }} <small class="text-muted">· {{ $s->usuarioApertura->nombre_completo ?? '' }}</small></td>
                                <td class="text-end"><a href="{{ route('caja.sesiones.show', $s) }}">Ver</a></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif

    @if (isset($d['transferencias_en_transito']) && $d['transferencias_en_transito']->isNotEmpty())
        <div class="col-lg-6 mt-2">
            <div class="card"><div class="card-body">
                <h6>Transferencias por recibir</h6>
                <table class="table table-sm small-table">
                    <tbody>
                        @foreach ($d['transferencias_en_transito'] as $t)
                            <tr><td>{{ $t->numero }} <small class="text-muted">→ {{ $t->almacenDestino->nombre }}</small></td>
                                <td class="text-end"><a href="{{ route('transferencias.show', $t) }}">Ver</a></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>
        </div>
    @endif
</div>

@endsection
