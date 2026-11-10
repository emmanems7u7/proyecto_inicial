@extends('layouts.argon')

@section('content')

    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Ventas y pedidos</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('ventas.crear')
                        <a href="{{ route('ventas.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nueva
                            venta</a>
                    @endcan
                    @can('caja.ver')
                        <a href="{{ route('caja.sesiones.index') }}" class="btn btn-sm btn-info"><i
                                class="fas fa-cash-register"></i> Caja</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Cada venta descuenta stock (primero lo que vence antes) y
                        registra el cobro en tu caja abierta.</small><br>
                    <small><i class="fas fa-clipboard-list me-1"></i>Los pedidos reservan la intención de compra; al
                        confirmarlos se descuenta el stock y se cobra.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')

                    <p class="mb-2"><small>Total de ventas completadas con estos filtros: <strong>Bs
                                {{ \App\Support\Formato::dinero($totalFiltrado) }}</strong></small></p>

                    <form method="GET" action="{{ route('ventas.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Número o cliente...">
                        </div>
                        <div class="col-md-2">
                            <select name="tienda_id" class="form-select form-select-sm">
                                <option value="">Todas las tiendas</option>
                                @foreach ($tiendas as $id => $nombre)
                                    <option value="{{ $id }}" @selected((string) request('tienda_id') === (string) $id)>{{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="completada" @selected(request('estado') === 'completada')>Completadas</option>
                                <option value="pedido" @selected(request('estado') === 'pedido')>Pedidos</option>
                                <option value="anulada" @selected(request('estado') === 'anulada')>Anuladas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="tipo_pago" class="form-select form-select-sm">
                                <option value="">Contado y crédito</option>
                                <option value="contado" @selected(request('tipo_pago') === 'contado')>Contado</option>
                                <option value="credito" @selected(request('tipo_pago') === 'credito')>Crédito</option>
                            </select>
                        </div>
                        @if ($vendedores->isNotEmpty())
                            <div class="col-md-2">
                                <select name="vendedor_id" class="form-select form-select-sm">
                                    <option value="">Todos los vendedores</option>
                                    @foreach ($vendedores as $id => $nombre)
                                        <option value="{{ $id }}" @selected((string) request('vendedor_id') === (string) $id)>{{ $nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <input type="date" name="desde" value="{{ request('desde') }}"
                                class="form-control form-control-sm" title="Desde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="hasta" value="{{ request('hasta') }}"
                                class="form-control form-control-sm" title="Hasta">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Tienda</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Pago</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ventas as $venta)
                                    <tr>
                                        <td>{{ $venta->numero }}</td>
                                        <td class="text-nowrap">{{ $venta->fecha->format('d/m/Y H:i') }}</td>
                                        <td>{{ $venta->tienda->nombre ?? '' }}</td>
                                        <td>{{ $venta->cliente->nombre ?? 'Consumidor final' }}</td>
                                        <td>{{ $venta->vendedor->nombre_completo ?? '—' }}</td>
                                        <td>{{ ucfirst($venta->tipo_pago) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($venta->total) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($venta->saldo) }}</td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', [
                                            'estado' => $venta->estado,
                                        ])</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('ventas.show', $venta) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver"><i
                                                    class="fas fa-eye"></i></a>
                                            <a href="{{ route('ventas.imprimir', $venta) }}" target="_blank"
                                                class="btn btn-xs btn-secondary mb-0 px-2 py-1" title="Imprimir"><i
                                                    class="fas fa-print"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-3">No se encontraron registros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $ventas->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
