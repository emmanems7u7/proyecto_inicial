<div class="row">
    @include('ventas.partials.campo_texto', ['name' => 'stock_minimo', 'label' => 'Stock mínimo', 'col' => 4, 'type' => 'number', 'step' => '0.001', 'min' => '0', 'value' => $inventario->stock_minimo, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'stock_maximo', 'label' => 'Stock máximo', 'col' => 4, 'type' => 'number', 'step' => '0.001', 'min' => '0', 'value' => $inventario->stock_maximo])
    @include('ventas.partials.campo_texto', ['name' => 'ubicacion', 'label' => 'Ubicación (estante / pasillo)', 'col' => 4, 'value' => $inventario->ubicacion, 'maxlength' => 60])
</div>
