<div class="row">
    @include('ventas.partials.campo_select', ['name' => 'cliente_id', 'label' => 'Cliente', 'col' => 6, 'value' => $cotizacion->cliente_id, 'options' => $clientes, 'placeholder' => '-- Sin cliente --'])
    @include('ventas.partials.campo_texto', ['name' => 'fecha_validez', 'label' => 'Válida hasta', 'col' => 3, 'type' => 'date', 'value' => optional($cotizacion->fecha_validez)->format('Y-m-d')])
</div>

@include('ventas.partials.lineas', ['modo' => 'cotizacion', 'productos' => $productos, 'items' => $items, 'descuento' => $cotizacion->descuento])

<div class="row mt-3">
    @include('ventas.partials.campo_textarea', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'value' => $cotizacion->observaciones, 'maxlength' => 1000])
</div>
