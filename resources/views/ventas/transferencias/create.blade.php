@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Nueva transferencia</h5>
                <small><i class="fas fa-info-circle me-1"></i>Se crea como pendiente; el stock sale del origen recién al enviarla.</small>

                <div class="mt-3">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('transferencias.store') }}" autocomplete="off">
                    @csrf
                    <div class="row">
                        @include('ventas.partials.campo_select', ['name' => 'almacen_origen_id', 'label' => 'Almacén de origen', 'col' => 5, 'required' => true, 'sinVacio' => true, 'value' => $origenId, 'options' => $opcionesOrigen])
                        @include('ventas.partials.campo_select', ['name' => 'almacen_destino_id', 'label' => 'Almacén de destino', 'col' => 5, 'required' => true, 'options' => $opcionesDestino])
                    </div>

                    @include('ventas.partials.lineas', ['modo' => 'transferencia', 'productos' => $productos, 'items' => $items, 'stock' => $stock, 'almacenSelectId' => 'almacen_origen_id'])

                    <div class="row mt-3">
                        @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'maxlength' => 1000])
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('transferencias.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Crear transferencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
