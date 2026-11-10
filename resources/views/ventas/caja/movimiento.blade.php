@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Movimiento manual de caja</h5>
                <small><i class="fas fa-info-circle me-1"></i>Para gastos, retiros, pagos menores o ingresos que no vienen de una venta.</small>

                <div class="mt-3">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('caja.movimiento.store', $sesion) }}" autocomplete="off">
                    @csrf
                    <div class="row">
                        @include('ventas.partials.campo_select', ['name' => 'tipo', 'label' => 'Tipo', 'col' => 4, 'required' => true, 'sinVacio' => true, 'options' => ['ingreso' => 'Ingreso', 'egreso' => 'Egreso']])
                        @include('ventas.partials.campo_texto', ['name' => 'monto', 'label' => 'Monto (Bs)', 'col' => 4, 'type' => 'number', 'step' => '0.01', 'min' => '0.01', 'required' => true])
                        @include('ventas.partials.campo_select', ['name' => 'metodo_pago_id', 'label' => 'Método', 'col' => 4, 'required' => true, 'options' => $metodos, 'sinVacio' => true])
                        @include('ventas.partials.campo_texto', ['name' => 'concepto', 'label' => 'Concepto', 'col' => 12, 'required' => true, 'maxlength' => 255])
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('caja.sesiones.show', $sesion) }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
