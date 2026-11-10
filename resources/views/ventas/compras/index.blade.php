@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Compras</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('compras.crear')
                        <a href="{{ route('compras.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nueva
                            compra</a>
                    @endcan
                    @can('ordenes_compra.ver')
                        <a href="{{ route('ordenes_compra.index') }}" class="btn btn-sm btn-info"><i
                                class="fas fa-file-alt"></i> Órdenes de compra</a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Al registrar una compra el stock ingresa al almacén (con
                        lote y vencimiento si el producto lo maneja).</small><br>
                    <small><i class="fas fa-hand-holding-usd me-1"></i>Las compras a crédito generan una cuenta por
                        pagar.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')

                    <p class="mb-2"><small>Total de compras recibidas con estos filtros: <strong>Bs
                                {{ \App\Support\Formato::dinero($totalFiltrado) }}</strong></small></p>

                    <form method="GET" action="{{ route('compras.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Número, factura o proveedor...">
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="recibida" @selected(request('estado') === 'recibida')>Recibidas</option>
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
                            <a href="{{ route('compras.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Doc. proveedor</th>
                                    <th>Pago</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($compras as $compra)
                                    <tr>
                                        <td>{{ $compra->numero }}</td>
                                        <td class="text-nowrap">{{ $compra->fecha->format('d/m/Y H:i') }}</td>
                                        <td>{{ $compra->proveedor->nombre ?? '—' }}</td>
                                        <td>{{ $compra->numero_documento_proveedor ?: '—' }}</td>
                                        <td>{{ ucfirst($compra->tipo_pago) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($compra->total) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($compra->saldo) }}</td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', [
                                            'estado' => $compra->estado,
                                        ])</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('compras.show', $compra) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver"><i
                                                    class="fas fa-eye"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">No se encontraron registros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $compras->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
