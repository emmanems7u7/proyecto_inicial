<div class="row">
    @include('ventas.partials.campo_texto', ['name' => 'razon_social', 'label' => 'Razón social', 'col' => 6, 'value' => $empresa->razon_social, 'required' => true, 'maxlength' => 150])
    @include('ventas.partials.campo_texto', ['name' => 'nombre_comercial', 'label' => 'Nombre comercial', 'col' => 6, 'value' => $empresa->nombre_comercial, 'maxlength' => 150])
    @include('ventas.partials.campo_texto', ['name' => 'nit', 'label' => 'NIT', 'col' => 4, 'value' => $empresa->nit, 'maxlength' => 30])
    @include('ventas.partials.campo_texto', ['name' => 'telefono', 'label' => 'Teléfono', 'col' => 4, 'value' => $empresa->telefono, 'maxlength' => 30])
    @include('ventas.partials.campo_texto', ['name' => 'email', 'label' => 'Correo', 'col' => 4, 'type' => 'email', 'value' => $empresa->email, 'maxlength' => 150])
    @include('ventas.partials.campo_select', ['name' => 'ciudad_id', 'label' => 'Ciudad', 'col' => 4, 'value' => $empresa->ciudad_id, 'options' => $ciudades])
    @include('ventas.partials.campo_texto', ['name' => 'direccion', 'label' => 'Dirección', 'col' => 8, 'value' => $empresa->direccion, 'maxlength' => 255])
</div>
