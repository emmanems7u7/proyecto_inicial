@extends('layouts.argon')

@section('content')
    <style>
        .is-invalid {
            border: 2px solid #dc3545 !important;
            background-color: #fff5f5;
        }
    </style>
    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">

                    <h2>Partidos Mundial 2026</h2>

                    <form action="{{ route('partidos.sincronizar') }}" method="POST">
                        @csrf

                        <button class="btn btn-success">
                            Sincronizar Partidos
                        </button>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>

    <div class="card mt-3 shadow-lg">
        <div class="card-body">

            <div class="row">



                @foreach ($partidos as $partido)
                    @php
                        $estado = match ($partido->estado) {
                            'NS' => 'Próximo',
                            'LIVE' => 'En Vivo',
                            'FT' => 'Finalizado',
                            'PST' => 'Pospuesto',
                            default => $partido->estado,
                        };

                        $badge = match ($partido->estado) {
                            'NS' => 'secondary',
                            'LIVE' => 'danger',
                            'FT' => 'success',
                            'PST' => 'warning',
                            default => 'info',
                        };
                    @endphp

                    <div class="col-lg-6 mb-4">

                        <div class="card shadow-sm border-0 mb-1">

                            <div class="card-body">

                                <div class="row align-items-center">

                                    {{-- Equipo local --}}
                                    <div class="col-4 text-center">

                                        <img src="{{ $partido->local->logo }}" style="height:60px">

                                        <div class="fw-bold small mt-2">
                                            {{ $partido->local->nombre }}
                                        </div>

                                    </div>

                                    {{-- Centro --}}
                                    <div class="col-4 text-center">

                                        <span class="badge bg-{{ $badge }}">
                                            {{ $estado }}
                                        </span>

                                        <div class="my-2">

                                            @if ($partido->estado == 'FT')
                                                <h4 class="mb-0">
                                                    {{ $partido->goles_local }}
                                                    -
                                                    {{ $partido->goles_visitante }}
                                                </h4>
                                            @else
                                                <h5 class="mb-0 text-primary">
                                                    VS
                                                </h5>
                                            @endif

                                        </div>

                                        <small class="text-muted d-block">
                                            <i class="fas fa-calendar-alt"></i>
                                            {{ \Carbon\Carbon::parse($partido->fecha)->format('d/m/Y') }}
                                        </small>

                                        <small class="text-muted d-block">
                                            <i class="fas fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($partido->hora)->subHours(4)->format('H:i') }}
                                        </small>

                                    </div>

                                    {{-- Equipo visitante --}}
                                    <div class="col-4 text-center">

                                        <img src="{{ $partido->visitante->logo }}" style="height:60px">

                                        <div class="fw-bold small mt-2">
                                            {{ $partido->visitante->nombre }}
                                        </div>

                                    </div>

                                </div>




                                <div class="row mb-3">

                                    <div class="col-6">

                                        <small class="text-muted d-block">
                                            Estadio
                                        </small>

                                        <small>
                                            <strong>
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                {{ $partido->estadio }}
                                            </strong>
                                        </small>

                                    </div>

                                    <div class="col-6">

                                        <small class="text-muted d-block">
                                            País
                                        </small>
                                        <small>
                                            <strong>
                                                <i class="fas fa-globe-americas text-success me-1"></i>
                                                {{ $partido->pais }}
                                            </strong>
                                        </small>

                                    </div>

                                </div>

                                <div class="row text-center mb-3">

                                    <div class="col-6">

                                        <div class="border rounded p-2">

                                            <small class="text-muted d-block">
                                                Total Apostado
                                            </small>

                                            <h6 class="text-success mb-0">
                                                Bs. {{ number_format($partido->apuestas_sum_monto ?? 0, 2) }}
                                            </h6>

                                        </div>

                                    </div>

                                    <div class="col-6">

                                        <div class="border rounded p-2">

                                            <small class="text-muted d-block">
                                                Apuestas
                                            </small>

                                            <h6 class="mb-0">
                                                {{ $partido->apuestas->count() }}
                                            </h6>

                                        </div>

                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <small class="text-muted d-block">
                                            Pozo acumulado <strong class="text-success">
                                                Bs. {{ $partido->apuestas()->sum('monto') }}
                                            </strong>
                                        </small>


                                    </div>




                                    <div class="col-12">

                                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse"
                                            data-bs-target="#apuestas{{ $partido->id }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>

                                        <button class="btn btn-primary btn-sm btn-apostar" data-id="{{ $partido->id }}"
                                            data-local="{{ $partido->local->nombre }}"
                                            data-local-logo="{{ $partido->local->logo }}"
                                            data-visitante="{{ $partido->visitante->nombre }}"
                                            data-visitante-logo="{{ $partido->visitante->logo }}">

                                            <i class="fas fa-coins me-1"></i>
                                            Apostar

                                        </button>

                                    </div>
                                </div>

                                {{-- Collapse --}}
                                <div class="collapse mt-3" id="apuestas{{ $partido->id }}">

                                    <h6 class="mb-3">
                                        <i class="fas fa-coins text-warning me-2"></i>
                                        Apuestas Registradas
                                    </h6>

                                    @forelse($partido->apuestas as $apuesta)
                                        @php

                                            $badgePago = match ($apuesta->estado_pago) {
                                                'aprobado' => 'success',
                                                'rechazado' => 'danger',
                                                default => 'warning',
                                            };

                                        @endphp

                                        <div class="card border-0 bg-light mb-2">

                                            <div class="card-body py-2">

                                                <div class="d-flex justify-content-between align-items-center">

                                                    <div>

                                                        <div class="fw-bold">
                                                            <small>
                                                                {{ $apuesta->nombre_completo }} -
                                                                {{ substr($apuesta->matricula, 0, 2) }}****
                                                            </small>

                                                        </div>

                                                        <div class="d-flex align-items-center gap-2 mt-1">
                                                            <small>
                                                                <span class="fw-bold text-success">
                                                                    Bs. {{ number_format($apuesta->monto, 2) }}
                                                                </span>
                                                            </small>
                                                            <small>

                                                                <span class="badge bg-{{ $badgePago }}">
                                                                    {{ ucfirst($apuesta->estado_pago) }}
                                                                </span>
                                                            </small>

                                                        </div>

                                                        <div>
                                                            <a href="{{ asset($apuesta->comprobante) }}" id=""
                                                                data-fancybox="preview_comprobante"
                                                                data-caption="Comprobante de Pago" class="text-primary">

                                                                <i class="fas fa-image me-1"></i>
                                                                Ver comprobante cargado

                                                            </a>


                                                        </div>
                                                        <div>
                                                            @if ($apuesta->estado_pago == 'pendiente')
                                                                <a href="{{ route('partidos.aprobar', $apuesta) }}"
                                                                    class="btn btn-xs btn-info">Aprobar</a>
                                                                <a href="{{ route('partidos.rechazar', $apuesta) }}"
                                                                    class="btn btn-xs btn-danger">Rechazar</a>
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>


                                                <div class="small">

                                                    <i class="fas fa-chart-line text-primary me-1"></i>
                                                    Pronóstico:

                                                </div>
                                                <div class="small text-center">

                                                    <strong>
                                                        {{ $partido->local->nombre }}
                                                    </strong>

                                                    {{ $apuesta->prediccion_local }}

                                                    -

                                                    {{ $apuesta->prediccion_visitante }}

                                                    <strong>
                                                        {{ $partido->visitante->nombre }}
                                                    </strong>
                                                </div>

                                            </div>

                                        </div>

                                    @empty

                                        <div class="alert alert-light border mb-0">

                                            <i class="fas fa-info-circle me-2"></i>

                                            No existen apuestas registradas para este partido.

                                        </div>
                                    @endforelse

                                </div>

                            </div>



                        </div>

                    </div>
                @endforeach

            </div>
        </div>
    </div>


    <div class="modal fade" id="modalApuesta" tabindex="-1">

        <div class="modal-dialog modal-lg modal-dialog-centered">

            <div class="modal-content border-0 shadow">

                <form action="{{ route('apuestas.store') }}" id="formApuesta" method="POST"
                    enctype="multipart/form-data">

                    @csrf

                    <input type="hidden" name="partido_id" id="partido_id">

                    <div class="modal-header">

                        <h5 class="modal-title">

                            <i class="fas fa-coins me-2"></i>
                            Registrar Apuesta

                        </h5>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        {{-- Pronóstico --}}
                        <h6 class="border-bottom pb-2 mb-3">

                            <i class="fas fa-chart-line me-2"></i>
                            Pronóstico del Marcador

                        </h6>

                        <div class="card bg-light border-0 mb-4">

                            <div class="card-body">

                                <div class="row g-3 align-items-center">

                                    {{-- Local --}}
                                    <div class="col-5">

                                        <div class="text-center">

                                            <img id="local_logo_pronostico" height="50" class="mb-2">

                                            <div id="local_nombre_pronostico" class="fw-bold small mb-3">
                                            </div>

                                            <input type="number" min="0" name="prediccion_local"
                                                id="prediccion_local"
                                                class="form-control form-control-lg text-center fw-bold" placeholder="0"
                                                required>

                                        </div>

                                    </div>

                                    {{-- VS --}}
                                    <div class="col-2">

                                        <div class="text-center">

                                            <span class="badge bg-dark rounded-pill px-3 py-2">
                                                VS
                                            </span>

                                        </div>

                                    </div>

                                    {{-- Visitante --}}
                                    <div class="col-5">

                                        <div class="text-center">

                                            <img id="visitante_logo_pronostico" height="50" class="mb-2">

                                            <div id="visitante_nombre_pronostico" class="fw-bold small mb-3">
                                            </div>

                                            <input type="number" min="0" name="prediccion_visitante"
                                                id="prediccion_visitante"
                                                class="form-control form-control-lg text-center fw-bold" placeholder="0"
                                                required>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- Datos apostador --}}
                        <h6 class="border-bottom pb-2 mb-3">

                            <i class="fas fa-user me-2"></i>
                            Datos requeridos

                        </h6>

                        <div class="row">

                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Matrícula
                                </label>

                                <input type="text" name="matricula" id="matricula" class="form-control"
                                    maxlength="20" required>

                            </div>

                            <div class="col-md-8 mb-3">

                                <label class="form-label">
                                    Nombre Completo
                                </label>

                                <input type="text" name="nombre_completo" id="nombre_completo" class="form-control"
                                    required>

                            </div>

                        </div>

                        {{-- Monto --}}

                        <div class="row align-items-end">

                            {{-- Monto --}}
                            <div class="col-6">

                                <div class="mb-3">

                                    <label class="form-label">

                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        Monto Apostado (Bs)

                                    </label>

                                    <input type="number" step="0.01" min="1" name="monto" id="monto"
                                        class="form-control" required>

                                </div>

                            </div>

                            {{-- Ver QR / Cuenta --}}
                            <div class="col-6">

                                <label class="form-label d-block">
                                    Datos de Pago
                                </label>

                                <a href="{{ asset('qr/qr_cobro.jpeg') }}" data-fancybox="informacion_pago"
                                    class="btn btn-outline-primary w-100">

                                    <i class="fas fa-qrcode me-1"></i>
                                    Ver QR
                                </a>

                            </div>

                        </div>

                        {{-- Comprobante --}}
                        <div class="mb-3">

                            <label class="form-label">

                                <i class="fas fa-receipt me-2"></i>
                                Comprobante de Pago

                            </label>

                            <input type="file" name="comprobante" id="comprobante" class="form-control"
                                accept="image/*" id="comprobante" required>

                            <small class="text-muted">
                                Adjunte una fotografía o captura del pago realizado.
                            </small>

                        </div>

                        <div class="mt-2 d-none" id="contenedorPreview">

                            <a href="#" id="previewComprobanteLink" data-fancybox="preview_comprobante"
                                data-caption="Comprobante de Pago" class="text-primary">

                                <i class="fas fa-image me-1"></i>
                                Ver comprobante cargado

                            </a>

                        </div>



                        <div class="alert alert-warning mt-4 mb-0">

                            <i class="fas fa-info-circle me-2"></i>

                            Su apuesta quedará registrada con estado de pago
                            <strong>PENDIENTE</strong> hasta que el comprobante sea revisado.

                            Al finalizar recibirá un código único para futuras modificaciones.

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">

                            Cancelar

                        </button>

                        <button type="submit" class="btn btn-primary">

                            <i class="fas fa-coins me-1"></i>
                            Registrar Apuesta

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="modal fade" id="modalExitoApuesta" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-body text-center p-4">

                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success fa-3x"></i>
                    </div>

                    <h5 class="mb-3">
                        ¡Apuesta registrada con éxito!
                    </h5>

                    <div class="alert alert-light border">

                        <small class="text-muted d-block mb-1">
                            Código único de tu apuesta
                        </small>

                        <h3 class="mb-0 text-primary" id="codigoApuestaFinal">
                            -----
                        </h3>

                    </div>

                    <p class="text-muted small">
                        Con este código podrás editar tu apuesta hasta 3 días antes del partido.
                    </p>

                    <button class="btn btn-outline-primary btn-sm mb-2" onclick="copiarCodigo()">
                        <i class="fas fa-copy me-1"></i>
                        Copiar código
                    </button>

                    <br>

                    <button class="btn btn-success w-100 mt-2" data-bs-dismiss="modal">
                        Entendido
                    </button>

                </div>

            </div>
        </div>
    </div>
@endsection
