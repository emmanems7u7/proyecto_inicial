<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mundial BCP</title>

    <link rel="icon" href="{{ asset('images/flecha.png') }}" type="image/png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('argon/css/argon-dashboard.css?v=2.1.0') }}" rel="stylesheet" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" crossorigin="" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />


    @vite(['resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>



    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
</head>



<style>
    #loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    /* Opcional: centrado con flex */
    #overlay-spinner {
        display: flex;
    }

    .alertify .ajs-modal {
        display: flex !important;
        justify-content: center;
        align-items: center;
    }

    .alertify .ajs-dialog {
        margin: 0 auto !important;

        transform: translateY(-40%) !important;
    }

    #navbarBlur {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1030;
        background: white;
    }
</style>

<div id="overlay-spinner"
    style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.5);
    z-index:9999;
    justify-content:center;
    align-items:center;
    flex-direction: column;
    color: white;
">
    <div class="spinner-border text-light mb-2" role="status"></div>
    <span>Cargando...</span>
</div>



<body class="{{ isset($preferencias) && $preferencias->dark_mode ? 'dark-version' : '' }} g-sidenav-show bg-gray-100">
    <main class="main-content position-relative border-radius-lg ">
        <nav class="navbar navbar-main navbar-expand-lg shadow-lg sticky-top bg-white" id="navbarBlur">
            <div class="container-fluid ">

                <!-- IZQUIERDA -->
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logo_bcp.png') }}" alt="logo" class="img-fluid"
                        style="max-height: 20px;">
                </div>

                <!-- CENTRO (opcional) -->
                <div class="mx-auto d-md-block">
                    <strong>
                        <span class="text-sm text-muted">
                            Mundial 2026
                        </span>
                    </strong>
                </div>



            </div>
        </nav>


        <img src="{{ asset('images/logo_m.png') }}" alt="logo" class="img-fluid  pt-5">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-4">

                <h5 class="fw-bold mb-2">
                    Bienvenido a la App de Apuestas BCP <i class="fas fa-trophy text-warning"></i>
                </h5>

                <p class="text-muted mb-3">
                    Vive la emoción del Mundial.
                    Realiza tus apuestas de forma rápida, segura y sencilla con tus equipos favoritos.
                </p>


            </div>
        </div>
        <div class="container">


            <div class="row mt-3">



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

                    <div class="col-lg-6 mb-2">

                        <div class="card shadow-sm border-0 mb-1">

                            <div class="card-body py-2 px-2">

                                <div class="position-absolute top-0 end-0 p-2">
                                    <i class="fas fa-info-circle text-primary" data-bs-toggle="tooltip"
                                        data-bs-html="true" data-bs-placement="left"
                                        title="
                                       <div class='text-start'>
                                            <strong>Fecha:</strong> {{ \Carbon\Carbon::parse($partido->fecha)->format('d/m/Y') }}<br>
                                            <strong>Hora:</strong> {{ substr($partido->hora, 0, 5) }}<br><br>
                                
                                            <strong>Estadio:</strong><br>
                                            <i class='fas fa-map-marker-alt text-danger me-1'></i> {{ $partido->estadio }}<br><br>
                                
                                            <strong>País:</strong><br>
                                            <i class='fas fa-globe-americas text-success me-1'></i> {{ $partido->pais }}
                                       </div>
                                       ">
                                    </i>
                                </div>

                                <div class="row align-items-center text-center">

                                    {{-- LOCAL --}}
                                    <div class="col-4">
                                        <img src="{{ $partido->local->logo }}" class="img-fluid"
                                            style="max-height:40px;">

                                        <div class="fw-bold small mt-1 text-truncate">
                                            {{ $partido->local->nombre }}
                                        </div>
                                    </div>

                                    {{-- CENTRO --}}
                                    <div class="col-4 px-1">

                                        <span class="badge bg-{{ $badge }} small">
                                            {{ $estado }}
                                        </span>

                                        <div class="my-0">

                                            @if ($partido->estado == 'FT')
                                                <h6 class="mb-0">
                                                    {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                                                </h6>
                                            @else
                                                <small class="text-primary fw-bold">VS</small>
                                            @endif

                                        </div>

                                    </div>

                                    {{-- VISITANTE --}}
                                    <div class="col-4">
                                        <img src="{{ $partido->visitante->logo }}" class="img-fluid"
                                            style="max-height:40px;">

                                        <div class="fw-bold small mt-1 text-truncate">
                                            {{ $partido->visitante->nombre }}
                                        </div>
                                    </div>

                                </div>


                                <div class="row mt-1">

                                    <div class="col-12">

                                        <button class="btn btn-outline-secondary btn-xs" data-bs-toggle="collapse"
                                            data-bs-target="#apuestas{{ $partido->id }}">
                                            <i class="fas fa-chevron-down"></i> Apuestas
                                        </button>

                                        <button class="btn btn-primary btn-xs btn-apostar"
                                            data-id="{{ $partido->id }}" data-local="{{ $partido->local->nombre }}"
                                            data-local-logo="{{ $partido->local->logo }}"
                                            data-visitante="{{ $partido->visitante->nombre }}"
                                            data-visitante-logo="{{ $partido->visitante->logo }}">

                                            <i class="fas fa-coins me-1"></i>
                                            Apostar

                                        </button>

                                    </div>
                                </div>

                                {{-- Collapse --}}
                                <div class="collapse mt-0" id="apuestas{{ $partido->id }}">
                                    <h6 class="mb-1">
                                        <i class="fas fa-coins text-yellow me-2"></i>
                                        Apuestas Existentes
                                    </h6>


                                    <div class="row">
                                        <div class="col-7 mb-3">
                                            <small class="text-muted d-block">
                                                Pozo acumulado <strong class="text-success">
                                                    Bs.
                                                    {{ $partido->apuestas()->where('estado_pago', 'aprobado')->sum('monto') }}
                                                </strong>
                                            </small>


                                        </div>

                                        <div class="col-5">
                                            <small class="text-muted d-block">
                                                Apuestas {{ $partido->apuestas->count() }}
                                            </small>


                                        </div>
                                    </div>

                                    @forelse($partido->apuestas as $apuesta)
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
                                                                <span
                                                                    class="fw-bold
                                                                    @if ($apuesta->estado_pago == 'aprobado') text-success
                                                                    @elseif($apuesta->estado_pago == 'pendiente') text-warning
                                                                    @elseif($apuesta->estado_pago == 'rechazado') text-danger @endif
                                                                ">
                                                                    Bs. {{ number_format($apuesta->monto, 2) }}
                                                                </span>

                                                                @if ($apuesta->estado_pago == 'pendiente' && !empty($apuesta->mensaje_validador))
                                                                    <i class="fas fa-exclamation-circle text-warning ms-1"
                                                                        data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        title="Validación manual de comprobante">
                                                                    </i>
                                                                @endif
                                                            </small>

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




        <div class="modal fade" id="modalApuesta" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false">

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

                            <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal"></button>

                        </div>

                        <div class="modal-body">

                            {{-- Pronóstico --}}
                            <h6 class="border-bottom pb-2 mb-3">

                                <i class="fas fa-chart-line me-2"></i>
                                Pronóstico del Marcador

                            </h6>

                            <div class="card bg-light border-0 mb-2 py-2">
                                <div class="card-body py-2">

                                    <div class="row g-3 align-items-center">

                                        {{-- Local --}}
                                        <div class="col-5">

                                            <div class="text-center">

                                                <img id="local_logo_pronostico" height="35" class="mb-2">

                                                <div id="local_nombre_pronostico" class="fw-bold small mb-1">
                                                </div>

                                                <input type="number" min="0" name="prediccion_local"
                                                    id="prediccion_local"
                                                    class="form-control form-control-sm text-center fw-bold"
                                                    placeholder="0" required>

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

                                                <img id="visitante_logo_pronostico" height="35" class="mb-2">

                                                <div id="visitante_nombre_pronostico" class="fw-bold small mb-1">
                                                </div>

                                                <input type="number" min="0" name="prediccion_visitante"
                                                    id="prediccion_visitante"
                                                    class="form-control form-control-sm text-center fw-bold"
                                                    placeholder="0" required>

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

                                <div class="col-4 mb-3">

                                    <label class="form-label">
                                        Matrícula
                                    </label>

                                    <input type="text" name="matricula" id="matricula"
                                        class="form-control form-control-sm text-center fw-bold" maxlength="20"
                                        required>

                                </div>

                                <div class="col-8 mb-3">

                                    <label class="form-label">
                                        Nombre Completo
                                    </label>

                                    <input type="text" name="nombre_completo" id="nombre_completo"
                                        class="form-control form-control-sm text-center fw-bold" required>

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

                                        <input type="number" step="0.01" min="1" name="monto"
                                            id="monto" class="form-control form-control-sm text-center fw-bold"
                                            required>

                                    </div>

                                </div>

                                {{-- Ver QR / Cuenta --}}
                                <div class="col-6">

                                    <label class="form-label d-block">
                                        Datos de Pago
                                    </label>

                                    <a href="{{ asset('qr/qr_cobro.jpeg') }}" data-fancybox="informacion_pago"
                                        class="btn btn-outline-primary btn-xs w-100">

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

                                <input type="file" name="comprobante" id="comprobante"
                                    class="form-control form-control-sm text-center fw-bold" accept="image/*"
                                    id="comprobante" required>

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



                            <div class="alert alert-warning mt-1 mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-2"></i>

                                    Su apuesta quedará registrada con estado de pago
                                    <strong>PENDIENTE</strong> hasta su validación.

                                    Si el pago se realiza mediante <strong>Yape</strong> o <strong>Banca Móvil
                                        BCP</strong>,
                                    la validación será <strong>automática</strong>.

                                    Si el comprobante proviene de otra aplicación o banco,
                                    la validación se realizará de forma <strong>manual</strong>.

                                </small>
                            </div>
                        </div>

                        <div class="modal-footer">

                            <button type="button" class="btn btn-outline-secondary btn-xs" data-bs-dismiss="modal">

                                Cancelar

                            </button>

                            <button type="submit" class="btn btn-primary btn-xs">

                                <i class="fas fa-coins me-1"></i>
                                Registrar Apuesta

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

        <div class="modal fade" id="modalExitoApuesta" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false">
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


    </main>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js" crossorigin=""></script>
    <!--   Core JS Files   -->
    <script src="{{ asset('argon/js/core/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('argon/js/plugins/chartjs.min.js') }}"></script>
    <script src="{{ asset('js/alertas.js') }}"></script>
    <script src="{{ asset('js/export.js') }}"></script>

    <script src="{{ asset('js/campos.js') }}"></script>
    <script src="{{ asset('js/iconos.js') }}"></script>
    <script src="{{ asset('js/offcanvas.js') }}"></script>
    <script src="{{ asset('js/apuestas.js') }}"></script>
    <script src="{{ asset('js/overlay.js') }}"></script>



    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <script src="{{ asset('argon/js/argon-dashboard.js?v=2.1.0') }}"></script>
</body>

</html>
