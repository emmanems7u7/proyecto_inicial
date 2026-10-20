<div class="row">
    @include('ventas.partials.campo_select', ['name' => 'tienda_id', 'label' => 'Tienda', 'col' => 6, 'value' => $almacen->tienda_id, 'options' => $tiendas, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'Código', 'col' => 3, 'value' => $almacen->codigo, 'required' => true, 'maxlength' => 20])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 3, 'value' => $almacen->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 6, 'value' => $almacen->nombre, 'required' => true, 'maxlength' => 120])
    @include('ventas.partials.campo_texto', ['name' => 'direccion', 'label' => 'Dirección', 'col' => 6, 'value' => $almacen->direccion, 'maxlength' => 255])
    @include('ventas.partials.campo_check', ['name' => 'es_principal', 'label' => 'Almacén principal de la tienda', 'col' => 6, 'value' => $almacen->es_principal])
</div>
