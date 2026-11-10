@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Caja {{ $sesion->caja->nombre }} - Sesión #{{ $sesion->id }}</h5>
                    <p class="mb-2">
                        @include('ventas.partials.badge_estado', ['estado' => $sesion->estado])
                        <small class="text-muted ms-1">{{ $sesion->caja->tienda->nombre }} ·
                            {{ $sesion->usuarioApertura->nombre_completo ?? '' }}</small>
                    </p>
                    <a href="{{ route('caja.sesiones.index') }}" class="btn btn-sm btn-secondary"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @if ($sesion->estado === 'abierta')
                        @can('caja.movimientos')
                            <a href="{{ route('caja.movimiento.create', $sesion) }}" class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus"></i> Movimiento manual</a>
                        @endcan
                        @can('caja.cerrar')
                            <a href="{{ route('caja.cerrar.create', $sesion) }}" class="btn btn-sm btn-danger"><i
                                    class="fas fa-lock"></i> Cerrar caja</a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <small>Apertura: <strong>{{ $sesion->fecha_apertura->format('d/m/Y H:i') }}</strong></small><br>
                    @if ($sesion->fecha_cierre)
                        <small>Cierre: <strong>{{ $sesion->fecha_cierre->format('d/m/Y H:i') }}</strong>
                            ({{ $sesion->tipo_cierre === 'automatico' ? 'automático' : 'manual por ' . ($sesion->usuarioCierre->nombre_completo ?? '—') }})</small><br>
                    @endif
                    @if ($sesion->observaciones)
                        <small>Obs.: {{ $sesion->observaciones }}</small><br>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        @php
            $tarjetas = [
                ['Efectivo inicial', $sesion->monto_apertura, 'secondary'],
                ['Ingresos', $sesion->total_ingresos, 'success'],
                ['Egresos', $sesion->total_egresos, 'danger'],
                ['Efectivo esperado', $esperado, 'info'],
            ];
            if ($sesion->estado === 'cerrada') {
                $tarjetas[] = ['Efectivo contado', $sesion->monto_real, 'primary'];
                $tarjetas[] = ['Diferencia', $sesion->diferencia, $sesion->diferencia == 0 ? 'success' : 'warning'];
            }
        @endphp
        @foreach ($tarjetas as [$etq, $monto, $color])
            <div class="col-6 col-lg-2 mb-2">
                <div class="card">
                    <div class="card-body p-3">
                        <small class="text-muted">{{ $etq }}</small>
                        <h6 class="mb-0 text-{{ $color }}">Bs {{ \App\Support\Formato::dinero($monto) }}</h6>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-4 mt-2">
            <div class="card">
                <div class="card-body">
                    <h6>Por método de pago</h6>
                    <table class="table table-sm small-table">
                        <thead>
                            <tr>
                                <th>Método</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Egresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($porMetodo as $metodoId => $t)
                                <tr>
                                    <td>{{ $metodos[$metodoId] ?? '—' }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($t['ingreso'] ?? 0) }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($t['egreso'] ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted text-center">Sin movimientos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <small class="text-muted">El efectivo esperado considera solo movimientos en efectivo.</small>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mt-2">
            <div class="card">
                <div class="card-body">
                    @include('ventas.partials.alertas')
                    <h6>Movimientos</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover small-table">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Método</th>
                                    <th class="text-end">Monto</th>
                                    <th>Usuario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movimientos as $m)
                                    <tr>
                                        <td class="text-nowrap">{{ $m->fecha->format('d/m H:i') }}</td>
                                        <td><span
                                                class="badge bg-gradient-{{ $m->tipo === 'ingreso' ? 'success' : 'danger' }}">{{ ucfirst($m->tipo) }}</span>
                                        </td>
                                        <td>{{ $m->concepto }}</td>
                                        <td>{{ $m->metodoPago->catalogo_descripcion ?? '—' }}</td>
                                        <td class="text-end">{{ \App\Support\Formato::dinero($m->monto) }}</td>
                                        <td>{{ $m->user->nombre_completo ?? 'Sistema' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Sin movimientos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center">
                        {{ $movimientos->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
