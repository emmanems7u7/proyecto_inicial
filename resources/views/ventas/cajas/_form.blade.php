<div class="row">
    @include('ventas.partials.campo_select', ['name' => 'tienda_id', 'label' => 'Tienda', 'col' => 5, 'value' => $caja->tienda_id, 'options' => $tiendas, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'Código', 'col' => 3, 'value' => $caja->codigo, 'required' => true, 'maxlength' => 20])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 4, 'value' => $caja->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 12, 'value' => $caja->nombre, 'required' => true, 'maxlength' => 100])
</div>
