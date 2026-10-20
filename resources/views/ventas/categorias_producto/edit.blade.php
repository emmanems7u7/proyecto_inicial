@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-9">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Editar categoría</h5>

                @include('ventas.partials.alertas')

                <form method="POST" action="{{ route('categorias_producto.update', $categoria) }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('ventas.categorias_producto._form')

                    <div class="d-flex gap-2">
                        <a href="{{ route('categorias_producto.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
