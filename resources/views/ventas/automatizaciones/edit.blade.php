@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>{{ $automatizacion->nombre }}</h5>
                <small>{{ $automatizacion->descripcion }}</small>
                @if ($automatizacion->tienda) <br><small class="text-muted">Tienda: {{ $automatizacion->tienda->nombre }}</small> @endif

                <div class="mt-3">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('automatizaciones.update', $automatizacion) }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('ventas.automatizaciones._form')

                    <div class="d-flex gap-2">
                        <a href="{{ route('automatizaciones.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
