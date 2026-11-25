@extends('layouts.argon')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Nueva tienda</h5>
                    @include('ventas.partials.alertas')

                    <form method="POST" action="{{ route('tiendas.store') }}" autocomplete="off">
                        @csrf
                        @include('ventas.tiendas._form')
                        <small class="text-muted d-block mb-3">Los productos son de la empresa; el stock, las cajas y los
                            usuarios son propios de cada tienda.</small>

                        <div class="d-flex gap-2">
                            <a href="{{ route('tiendas.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-arrow-right"></i> Crear
                                tienda y continuar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h6>Para dejarla lista</h6>
                    <ol class="small ps-3 mb-0">
                        <li class="fw-bold">Datos de la tienda <span class="badge bg-gradient-warning">En curso</span></li>
                        <li>Crear almacén principal (automático)</li>
                        <li>Crear administrador de la tienda</li>
                        <li>Crear caja</li>
                        <li>Cargar productos</li>
                        <li>Cargar stock inicial</li>
                    </ol>
                    <small class="text-muted">Al crear la tienda continuarás con estos pasos.</small>
                </div>
            </div>
        </div>
    </div>
@endsection
