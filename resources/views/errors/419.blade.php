@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="card shadow-lg border-0 rounded">
                    <div class="card-body">
                        <h1 class="display-1 text-info">
                            <i class="fas fa-hourglass-end"></i> 419
                        </h1>
                        <h3 class="text-uppercase">Sesión Expirada</h3>
                        <p class="lead">Tu sesión ha expirado. Por favor, vuelve a cargar la página e intenta de nuevo.</p>
                        <a href="{{ url()->previous() }}" class="btn btn-primary mt-4">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection