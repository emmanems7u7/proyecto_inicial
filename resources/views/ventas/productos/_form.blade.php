<div class="row">
    @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'Código', 'col' => 3, 'value' => $producto->codigo, 'required' => true, 'maxlength' => 50])
    @include('ventas.partials.campo_texto', ['name' => 'codigo_barras', 'label' => 'Código de barras', 'col' => 3, 'value' => $producto->codigo_barras, 'maxlength' => 60])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 6, 'value' => $producto->nombre, 'required' => true, 'maxlength' => 180])
    @include('ventas.partials.campo_select', ['name' => 'categoria_producto_id', 'label' => 'Categoría', 'col' => 4, 'value' => $producto->categoria_producto_id, 'options' => $categorias])
    @include('ventas.partials.campo_select', ['name' => 'unidad_medida_id', 'label' => 'Unidad de medida', 'col' => 4, 'value' => $producto->unidad_medida_id, 'options' => $unidades, 'required' => true])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 4, 'value' => $producto->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'precio_compra', 'label' => 'Precio de compra (Bs)', 'col' => 4, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => $producto->precio_compra, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'precio_venta', 'label' => 'Precio de venta (Bs)', 'col' => 4, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => $producto->precio_venta, 'required' => true])
    <div class="col-md-4"></div>
    @include('ventas.partials.campo_check', ['name' => 'maneja_lotes', 'label' => 'Controlar por lotes', 'col' => 4, 'value' => $producto->maneja_lotes])
    @include('ventas.partials.campo_check', ['name' => 'maneja_vencimiento', 'label' => 'Controlar fecha de vencimiento', 'col' => 4, 'value' => $producto->maneja_vencimiento])
    @include('ventas.partials.campo_textarea', ['name' => 'descripcion', 'label' => 'Descripción', 'col' => 12, 'value' => $producto->descripcion, 'maxlength' => 2000])
</div>
