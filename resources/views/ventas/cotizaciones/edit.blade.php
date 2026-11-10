@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Editar cotización</h5>

                @include('ventas.partials.alertas')

                <form method="POST" action="{{ route('cotizaciones.update', $cotizacion) }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('ventas.cotizaciones._form')

                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('cotizaciones.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
