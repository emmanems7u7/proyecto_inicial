@extends('layouts.argon')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Editar: {{ $producto->nombre }}</h5>
            <a href="{{ route('productos.index') }}" class="btn btn-sm btn-secondary mb-0"><i class="fas fa-arrow-left me-1"></i>Volver</a>
        </div>
        @include('ventas.partials.alertas')
        @include('ventas.productos._form', [
            'accion' => route('productos.update', $producto), 'metodo' => 'PUT',
            'cancelar' => route('productos.index'), 'conOtro' => false,
        ])
    </div>
</div>
@endsection
