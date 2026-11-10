@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Nueva compra - {{ $tienda->nombre }}</h5>
                @if ($orden)
                    <small><i class="fas fa-file-alt me-1"></i>Recepción de la orden {{ $orden->numero }} (se sugieren las cantidades pendientes).</small>
                @endif

                <div class="mt-2">@include('ventas.partials.alertas')</div>

                <form method="POST" action="{{ route('compras.store') }}" autocomplete="off">
                    @csrf
                    @if ($orden) <input type="hidden" name="orden_compra_id" value="{{ $orden->id }}"> @endif

                    <div class="row">
                        @include('ventas.partials.campo_select', ['name' => 'proveedor_id', 'label' => 'Proveedor', 'col' => 4, 'required' => true, 'value' => $proveedorId, 'options' => $proveedores])
                        @include('ventas.partials.campo_select', ['name' => 'almacen_id', 'label' => 'Almacén de ingreso', 'col' => 3, 'required' => true, 'sinVacio' => true, 'value' => $almacenId, 'options' => $opcionesAlmacen])
                        @include('ventas.partials.campo_texto', ['name' => 'numero_documento_proveedor', 'label' => 'N.º factura / nota del proveedor', 'col' => 3, 'maxlength' => 40])
                        @include('ventas.partials.campo_select', ['name' => 'tipo_pago', 'label' => 'Tipo de pago', 'col' => 2, 'required' => true, 'sinVacio' => true, 'value' => 'contado', 'options' => ['contado' => 'Contado', 'credito' => 'Crédito']])
                    </div>

                    <div class="row" id="bloque_contado">
                        @include('ventas.partials.campo_check', ['name' => 'pagar_caja', 'label' => 'Registrar el pago como egreso de mi caja abierta', 'col' => 6, 'value' => false])
                        @include('ventas.partials.campo_select', ['name' => 'metodo_pago_id', 'label' => 'Método de pago', 'col' => 3, 'value' => $efectivoId, 'options' => $metodos, 'placeholder' => '-- Seleccione --'])
                        @unless ($sesion)
                            <div class="col-md-3 mb-3 d-flex align-items-end"><small class="text-muted">No tienes caja abierta.</small></div>
                        @endunless
                    </div>

                    @include('ventas.partials.lineas', ['modo' => 'compra', 'productos' => $productos, 'items' => $items])

                    <div class="row mt-3">
                        @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'maxlength' => 1000])
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('compras.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Registrar compra</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipo = document.getElementById('tipo_pago');
    const bloque = document.getElementById('bloque_contado');
    const alternar = () => { bloque.style.display = tipo.value === 'credito' ? 'none' : ''; };
    tipo.addEventListener('change', alternar);
    alternar();
});
</script>

@endsection
