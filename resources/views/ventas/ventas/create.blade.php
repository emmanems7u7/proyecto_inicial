@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Nueva venta - {{ $tienda->nombre }}</h5>
                @if ($cotizacion)
                    <small><i class="fas fa-file-invoice me-1"></i>Desde la cotización {{ $cotizacion->numero }}</small>
                @endif

                <div class="mt-2">@include('ventas.partials.alertas')</div>

                @unless ($sesion)
                    <div class="alert alert-warning text-white py-2">
                        No tienes una caja abierta: podrás guardar pedidos o ventas a crédito sin anticipo.
                        @can('caja.abrir') <a href="{{ route('caja.abrir.create') }}" class="text-white"><u>Abrir caja</u></a> @endcan
                    </div>
                @endunless

                <form method="POST" action="{{ route('ventas.store') }}" autocomplete="off">
                    @csrf
                    @if ($cotizacion)
                        <input type="hidden" name="cotizacion_id" value="{{ $cotizacion->id }}">
                    @endif

                    <div class="row">
                        @include('ventas.partials.campo_select', ['name' => 'almacen_id', 'label' => 'Almacén', 'col' => 3, 'required' => true, 'sinVacio' => true, 'value' => $almacenId, 'options' => $opcionesAlmacen])
                        @include('ventas.partials.campo_select', ['name' => 'cliente_id', 'label' => 'Cliente', 'col' => 4, 'value' => $clienteId, 'options' => $clientes, 'placeholder' => '-- Consumidor final --'])
                        @include('ventas.partials.campo_select', ['name' => 'tipo_pago', 'label' => 'Tipo de pago', 'col' => 2, 'required' => true, 'sinVacio' => true, 'value' => 'contado', 'options' => ['contado' => 'Contado', 'credito' => 'Crédito']])
                        @include('ventas.partials.campo_select', ['name' => 'metodo_pago_id', 'label' => 'Método de pago', 'col' => 3, 'value' => $efectivoId, 'options' => $metodos, 'placeholder' => '-- Seleccione --'])
                    </div>
                    <div class="row" id="bloque_anticipo" style="display: none;">
                        @include('ventas.partials.campo_texto', ['name' => 'anticipo', 'label' => 'Anticipo (Bs) - opcional', 'col' => 3, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => '0'])
                        <div class="col-md-9 mb-3 d-flex align-items-end"><small class="text-muted">La venta a crédito requiere un cliente con límite de crédito. El saldo queda en Cuentas por cobrar.</small></div>
                    </div>

                    @include('ventas.partials.lineas', ['modo' => 'venta', 'productos' => $productos, 'items' => $items, 'stock' => $stock, 'descuento' => $descuento])

                    <div class="row mt-3">
                        @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 8, 'maxlength' => 1000])
                        @include('ventas.partials.campo_check', ['name' => 'como_pedido', 'label' => 'Guardar como pedido (no descuenta stock ni cobra)', 'col' => 4, 'value' => false])
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check"></i> Registrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipo = document.getElementById('tipo_pago');
    const bloque = document.getElementById('bloque_anticipo');
    const alternar = () => { bloque.style.display = tipo.value === 'credito' ? '' : 'none'; };
    tipo.addEventListener('change', alternar);
    alternar();
});
</script>

@endsection
