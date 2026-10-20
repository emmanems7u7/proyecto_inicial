@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Registrar movimiento de inventario</h5>
                    <small><i class="fas fa-info-circle me-1"></i>Usa este formulario para stock inicial, ingresos, bajas y
                        ajustes. Las ventas y compras lo harán automáticamente en la siguiente fase.</small>

                    <div class="mt-3">
                        @include('ventas.partials.alertas')
                    </div>

                    <form method="POST" action="{{ route('inventario.movimiento.store') }}" autocomplete="off">
                        @csrf
                        @include('ventas.inventario._form_movimiento')

                        <div class="d-flex gap-2">
                            <a href="{{ route('inventario.index', ['almacen_id' => $almacenId]) }}"
                                class="btn btn-sm btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i>
                                Registrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
