@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-9">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Nuevo producto</h5>

                @include('ventas.partials.alertas')

                <form method="POST" action="{{ route('productos.store') }}" autocomplete="off">
                    @csrf
                    @include('ventas.productos._form')

                    <div class="d-flex gap-2">
                        <a href="{{ route('productos.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
