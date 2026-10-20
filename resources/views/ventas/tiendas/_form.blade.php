<div class="row">
    @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'Código', 'col' => 4, 'value' => $tienda->codigo, 'required' => true, 'maxlength' => 20])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 8, 'value' => $tienda->nombre, 'required' => true, 'maxlength' => 120])
    @include('ventas.partials.campo_texto', ['name' => 'direccion', 'label' => 'Dirección', 'col' => 12, 'value' => $tienda->direccion, 'maxlength' => 255])
    @include('ventas.partials.campo_texto', ['name' => 'telefono', 'label' => 'Teléfono', 'col' => 4, 'value' => $tienda->telefono, 'maxlength' => 30])
    @include('ventas.partials.campo_texto', ['name' => 'email', 'label' => 'Correo', 'col' => 4, 'type' => 'email', 'value' => $tienda->email, 'maxlength' => 150])
    @include('ventas.partials.campo_select', ['name' => 'ciudad_id', 'label' => 'Ciudad', 'col' => 4, 'value' => $tienda->ciudad_id, 'options' => $ciudades])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 4, 'value' => $tienda->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_check', ['name' => 'es_matriz', 'label' => 'Es la tienda matriz', 'col' => 4, 'value' => $tienda->es_matriz])
</div>
