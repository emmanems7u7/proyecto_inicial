@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="card shadow-lg border-0 rounded">
                    <div class="card-body">
                        <h1 class="display-1 text-danger">
                            <i class="fas fa-server"></i> 500
                        </h1>
                        <h3 class="text-uppercase">Error Interno del Servidor</h3>
                        <p class="lead">Algo salió mal en el servidor. Por favor, intenta más tarde.</p>
                        <a href="{{ url('/') }}" class="btn btn-primary mt-4">
                            <i class="fas fa-home"></i> Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection