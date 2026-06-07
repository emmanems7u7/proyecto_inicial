<!--
=========================================================
* Argon Dashboard 3 - v2.1.0
=========================================================

* Product Page: https://www.creative-tim.com/product/argon-dashboard
* Copyright 2024 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/apple-icon.png')  }}">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="{{ asset('argon/css/nucleo-icons.css')  }}" rel="stylesheet" />
    <link href="{{ asset('argon/css/nucleo-svg.css')  }}" rel="stylesheet" />

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="{{ asset('argon/css/argon-dashboard.css?v=2.1.0')  }}" rel="stylesheet" />

</head>
<style>
    <style>.form-group .form-control {
        margin-right: 10px;
        /* Espacio a la derecha de cada campo */
        width: 50px;
        /* Puedes ajustar el tamaño de cada input */
    }

    /* También se puede ajustar el espacio en pantallas pequeñas */
    @media (max-width: 600px) {
        .form-group .form-control {
            margin-right: 5px;
            /* Menos espacio en pantallas pequeñas */
        }
    }
</style>
</style>

<body class="">
    <div class="container position-sticky z-index-sticky top-0">
        <div class="row">
            <div class="col-12">
                <!-- Navbar -->
                <nav
                    class="navbar navbar-expand-lg blur border-radius-lg top-0 z-index-3 shadow position-absolute mt-4 py-2 start-0 end-0 mx-4">
                    <div class="container-fluid">
                        <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 " href="../pages/dashboard.html">
                            {{ config('app.name', 'Laravel') }}
                        </a>
                        <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon mt-2">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </span>
                        </button>
                        <div class="collapse navbar-collapse" id="navigation">
                            <ul class="navbar-nav mx-auto">
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center me-2 active" aria-current="page"
                                        href="../pages/dashboard.html">
                                        <i class="fa fa-chart-pie opacity-6 text-dark me-1"></i>
                                        Inicio
                                    </a>
                                </li>


                            </ul>

                        </div>
                    </div>
                </nav>
                <!-- End Navbar -->
            </div>
        </div>
    </div>
    <main class="main-content  mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
                            <div class="card card-plain">
                                <div class="card-header pb-0 text-start">
                                    <h4 class="font-weight-bolder">Verificación de código</h4>
                                    <p class="mb-0">Verifica en tu correo el codigo enviado</p>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('verify.store') }}">
                                        @csrf
                                        <div class="mb-3 ">
                                            <div class="form-group">
                                                <label for="code">Ingresa el código enviado a tu correo:</label>
                                                <div class="d-flex justify-content-center">
                                                    <!-- 6 campos con margen derecho usando Bootstrap -->
                                                    <input type="text" id="code1"
                                                        class="form-control form-control-lg @error('code') is-invalid @enderror me-2"
                                                        maxlength="1" name="code[]" value="" aria-label="Código 1"
                                                        oninput="moveFocus(this, 'code2')" />
                                                    <input type="text" id="code2"
                                                        class="form-control form-control-lg @error('code') is-invalid @enderror me-2"
                                                        maxlength="1" name="code[]" value="" aria-label="Código 2"
                                                        oninput="moveFocus(this, 'code3')" />
                                                    <input type="text" id="code3"
                                                        class="form-control form-control-lg @error('code') is-invalid @enderror me-2"
                                                        maxlength="1" name="code[]" value="" aria-label="Código 3"
                                                        oninput="moveFocus(this, 'code4')" />
                                                    <input type="text" id="code4"
                                                        class="form-control form-control-lg @error('code') is-invalid @enderror me-2"
                                                        maxlength="1" name="code[]" value="" aria-label="Código 4"
                                                        oninput="moveFocus(this, 'code5')" />
                                                    <input type="text" id="code5"
                                                        class="form-control form-control-lg @error('code') is-invalid @enderror me-2"
                                                        maxlength="1" name="code[]" value="" aria-label="Código 5"
                                                        oninput="moveFocus(this, 'code6')" />
                                                    <input type="text" id="code6"
                                                        class="form-control form-control-lg @error('code') is-invalid @enderror"
                                                        maxlength="1" name="code[]" value="" aria-label="Código 6" />
                                                </div>

                                                @error('code')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>

                                        <div class="text-center">
                                            <button type="submit"
                                                class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Verificar</button>

                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                                    <form method="POST" action="{{ route('verify.resend') }}" class="mt-3">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary">Reenviar código</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
                            <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden"
                                style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/signin-ill.jpg');
      background-size: cover;">
                                <span class="mask bg-gradient-primary opacity-6"></span>
                                <h4 class="mt-5 text-white font-weight-bolder position-relative">"La atención es la
                                    nueva moneda"</h4>
                                <p class="text-white position-relative">Cuanto más fácil parece escribir, más esfuerzo
                                    ha puesto el escritor en el proceso.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <script src="{{asset('argon/js/core/popper.min.js')}}"></script>
    <script src="{{asset('argon/js/core/bootstrap.min.js')}}"></script>

    <script src="{{asset('argon/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('argon/js/plugins/smooth-scrollbar.min.js')}}"></script>
    <script src="{{asset('argon/js/plugins/chartjs.min.js')}}"></script>
    <script>
        // Función para mover el foco al siguiente campo
        function moveFocus(current, nextFieldId) {
            if (current.value.length == current.maxLength) {
                const nextField = document.getElementById(nextFieldId);
                if (nextField) {
                    nextField.focus();
                }
            }
        }
    </script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="../assets/js/argon-dashboard.min.js?v=2.1.0"></script>
</body>

</html>