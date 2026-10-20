@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Mi empresa</h5>
                    <small><i class="fas fa-info-circle me-1"></i>Datos generales de la empresa. Las tiendas o sucursales se
                        administran en su propio módulo.</small>

                    <div class="mt-3">
                        @include('ventas.partials.alertas')
                    </div>

                    <form method="POST" action="{{ route('empresa.update') }}" autocomplete="off">
                        @csrf
                        @method('PUT')
                        @include('ventas.empresa._form')

                        @can('empresa.editar')
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar
                                cambios</button>
                        @endcan
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
