@extends('layouts.argon')

@section('content')

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Abrir caja - {{ $tienda->nombre }}</h5>
                <small><i class="fas fa-info-circle me-1"></i>Registra el efectivo inicial con el que empiezas el turno.</small>

                <div class="mt-3">@include('ventas.partials.alertas')</div>

                @if ($cajas->isEmpty())
                    <div class="alert alert-warning text-white py-2">No hay cajas libres en esta tienda. Pide que configuren una caja o cierra la que está abierta.</div>
                @else
                    <form method="POST" action="{{ route('caja.abrir.store') }}" autocomplete="off">
                        @csrf
                        <div class="row">
                            @include('ventas.partials.campo_select', ['name' => 'caja_id', 'label' => 'Caja', 'col' => 6, 'required' => true, 'options' => $cajas, 'sinVacio' => true])
                            @include('ventas.partials.campo_texto', ['name' => 'monto_apertura', 'label' => 'Efectivo inicial (Bs)', 'col' => 6, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'required' => true, 'value' => '0'])
                            @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'maxlength' => 500])
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('caja.sesiones.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-lock-open"></i> Abrir caja</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
