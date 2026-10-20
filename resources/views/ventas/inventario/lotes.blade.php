@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Lotes y vencimientos</h5>
                <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>Solo aparecen productos con control por lotes. "Por vencer" considera los próximos {{ \App\Models\Lote::DIAS_ALERTA }} días.</small><br>
                <small><i class="fas fa-sort-amount-down me-1"></i>Las salidas descuentan primero los lotes que vencen antes (FEFO).</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('inventario.lotes') }}" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="almacen_id" class="form-select form-select-sm">
                            <option value="">Todos los almacenes</option>
                            @foreach ($opcionesAlmacen as $id => $nombre)
                                <option value="{{ $id }}" @selected((string) request('almacen_id') === (string) $id)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Producto o n.º de lote...">
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="vigente" @selected(request('estado') === 'vigente')>Vigentes</option>
                            <option value="por_vencer" @selected(request('estado') === 'por_vencer')>Por vencer</option>
                            <option value="vencido" @selected(request('estado') === 'vencido')>Vencidos</option>
                            <option value="agotado" @selected(request('estado') === 'agotado')>Agotados</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('inventario.lotes') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Almacén</th>
                                <th>Lote</th>
                                <th>Ingreso</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Cant. inicial</th>
                                <th class="text-end">Cant. actual</th>
                                <th class="text-end">Costo</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lotes as $lote)
                                @php
                                    $estado = $lote->estado_vencimiento;
                                    $mapa = [
                                        'agotado' => ['secondary', 'Agotado'],
                                        'sin_vencimiento' => ['info', 'Sin vencimiento'],
                                        'vencido' => ['danger', 'Vencido'],
                                        'por_vencer' => ['warning', 'Por vencer'],
                                        'vigente' => ['success', 'Vigente'],
                                    ];
                                @endphp
                                <tr>
                                    <td>{{ $lote->producto->codigo }} - {{ $lote->producto->nombre }}</td>
                                    <td>{{ $lote->almacen->nombre_completo ?? '—' }}</td>
                                    <td>{{ $lote->numero_lote }}</td>
                                    <td>{{ $lote->fecha_ingreso?->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $lote->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                                        @if ($lote->dias_para_vencer !== null && $lote->cantidad_actual > 0)
                                            <small class="text-muted">({{ $lote->dias_para_vencer }} d)</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ \App\Support\Formato::cantidad($lote->cantidad_inicial) }}</td>
                                    <td class="text-end"><strong>{{ \App\Support\Formato::cantidad($lote->cantidad_actual) }}</strong></td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($lote->costo_unitario) }}</td>
                                    <td class="text-center"><span class="badge bg-gradient-{{ $mapa[$estado][0] }}">{{ $mapa[$estado][1] }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-3">No hay lotes con esos filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $lotes->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
