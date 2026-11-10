@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Cuentas por pagar</h5>

                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small><i class="fas fa-info-circle me-1"></i>Documentos a crédito con tus proveedores. Registra cada
                        pago para actualizar el saldo.</small><br>
                    <small><i class="fas fa-robot me-1"></i>Los estados vencidos y los avisos por correo se actualizan
                        automáticamente.</small><br>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mt-2">
            <div class="card">
                <div class="card-body">

                    @include('ventas.partials.alertas')

                    <div class="row mb-2">
                        <div class="col-6 col-md-3">
                            <div class="card">
                                <div class="card-body p-3"><small class="text-muted">Saldo pendiente</small>
                                    <h6 class="mb-0">Bs {{ \App\Support\Formato::dinero($resumen['saldo']) }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card">
                                <div class="card-body p-3"><small class="text-muted">Saldo vencido</small>
                                    <h6 class="mb-0 text-danger">Bs {{ \App\Support\Formato::dinero($resumen['vencido']) }}
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('cxp.index') }}" class="row g-2 mb-3">
                        <div class="col-md-3">
                            <input type="text" name="q" value="{{ $q }}"
                                class="form-control form-control-sm" placeholder="Documento o proveedor...">
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-select form-select-sm">
                                <option value="abiertas" @selected($estado === 'abiertas')>Con saldo</option>
                                <option value="vencidas" @selected($estado === 'vencidas')>Vencidas</option>
                                <option value="pagadas" @selected($estado === 'pagadas')>Pagadas</option>
                                <option value="todas" @selected($estado === 'todas')>Todas</option>
                            </select>
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
                            <input type="date" name="desde" value="{{ request('desde') }}"
                                class="form-control form-control-sm" title="Vence desde">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="hasta" value="{{ request('hasta') }}"
                                class="form-control form-control-sm" title="Vence hasta">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                            <a href="{{ route('cxp.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-items-center small-table">
                            <thead>
                                <tr>
                                    <th>Documento</th>
                                    <th>Proveedor</th>
                                    <th>Tienda</th>
                                    <th>Emisión</th>
                                    <th>Vence</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cuentas as $cuenta)
                                    <tr>
                                        <td>{{ $cuenta->numero_documento }}</td>
                                        <td><a
                                                href="{{ route('cxp.proveedor', $cuenta->proveedor) }}">{{ $cuenta->proveedor->nombre ?? '—' }}</a>
                                        </td>
                                        <td>{{ $cuenta->tienda->nombre ?? '' }}</td>
                                        <td>{{ $cuenta->fecha_emision->format('d/m/Y') }}</td>
                                        <td>{{ $cuenta->fecha_vencimiento->format('d/m/Y') }}@if ($cuenta->dias_vencido > 0 && $cuenta->saldo > 0)
                                                <small class="text-danger">({{ $cuenta->dias_vencido }} d)</small>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($cuenta->monto_total) }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($cuenta->saldo) }}</td>
                                        <td class="text-center">@include('ventas.partials.badge_estado', [
                                            'estado' => $cuenta->estado_efectivo,
                                        ])</td>
                                        <td class="text-end text-nowrap">
                                            <a href="{{ route('cxp.show', $cuenta) }}"
                                                class="btn btn-xs btn-info mb-0 px-2 py-1" title="Ver / registrar pago"><i
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
                            {{ $cuentas->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
