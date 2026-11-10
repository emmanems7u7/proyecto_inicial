@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Nueva orden de compra - {{ $tienda->nombre }}</h5>

                <div class="mt-2">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('ordenes_compra.store') }}" autocomplete="off">
                    @csrf
                    <div class="row">
                        @include('ventas.partials.campo_select', ['name' => 'proveedor_id', 'label' => 'Proveedor', 'col' => 5, 'required' => true, 'options' => $proveedores])
                        @include('ventas.partials.campo_select', ['name' => 'almacen_id', 'label' => 'Almacén de destino', 'col' => 4, 'required' => true, 'sinVacio' => true, 'value' => $almacenId, 'options' => $opcionesAlmacen])
                        @include('ventas.partials.campo_texto', ['name' => 'fecha_entrega_estimada', 'label' => 'Entrega estimada', 'col' => 3, 'type' => 'date'])
                    </div>

                    @include('ventas.partials.lineas', ['modo' => 'orden', 'productos' => $productos, 'items' => $items])

                    <div class="row mt-3">
                        @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'maxlength' => 1000])
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('ordenes_compra.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Guardar orden</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
