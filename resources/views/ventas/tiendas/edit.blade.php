@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-9">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Editar tienda</h5>

                @include('ventas.partials.alertas')

                <form method="POST" action="{{ route('tiendas.update', $tienda) }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    @include('ventas.tiendas._form')

                    <div class="d-flex gap-2">
                        <a href="{{ route('tiendas.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
