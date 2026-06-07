{{-- resources/views/errors/404.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="card shadow-lg border-0 rounded">
                    <div class="card-body">
                        <h1 class="display-1 text-danger">
                            <i class="fas fa-exclamation-triangle"></i> 404
                        </h1>
                        <h3 class="text-uppercase">Página no encontrada</h3>
                        <p class="lead">Lo sentimos, la página que buscas no existe.</p>
                        <a href="{{ url('/') }}" class="btn btn-primary mt-4">
                            <i class="fas fa-home"></i> Volver al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection