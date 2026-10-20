<div class="row">
    @include('ventas.partials.campo_select', ['name' => 'tipo_documento_id', 'label' => 'Tipo de documento', 'col' => 4, 'value' => $proveedor->tipo_documento_id, 'options' => $tiposDocumento])
    @include('ventas.partials.campo_texto', ['name' => 'numero_documento', 'label' => 'Número de documento', 'col' => 5, 'value' => $proveedor->numero_documento, 'maxlength' => 30])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 3, 'value' => $proveedor->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre / Razón social', 'col' => 8, 'value' => $proveedor->nombre, 'required' => true, 'maxlength' => 180])
    @include('ventas.partials.campo_texto', ['name' => 'contacto_nombre', 'label' => 'Persona de contacto', 'col' => 4, 'value' => $proveedor->contacto_nombre, 'maxlength' => 120])
    @include('ventas.partials.campo_texto', ['name' => 'telefono', 'label' => 'Teléfono', 'col' => 4, 'value' => $proveedor->telefono, 'maxlength' => 30])
    @include('ventas.partials.campo_texto', ['name' => 'email', 'label' => 'Correo', 'col' => 4, 'type' => 'email', 'value' => $proveedor->email, 'maxlength' => 150])
    @include('ventas.partials.campo_select', ['name' => 'ciudad_id', 'label' => 'Ciudad', 'col' => 4, 'value' => $proveedor->ciudad_id, 'options' => $ciudades])
    @include('ventas.partials.campo_texto', ['name' => 'direccion', 'label' => 'Dirección', 'col' => 8, 'value' => $proveedor->direccion, 'maxlength' => 255])
    @include('ventas.partials.campo_texto', ['name' => 'dias_credito', 'label' => 'Días de crédito', 'col' => 4, 'type' => 'number', 'step' => '1', 'min' => '0', 'value' => $proveedor->dias_credito, 'required' => true])
    @include('ventas.partials.campo_textarea', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'value' => $proveedor->observaciones, 'maxlength' => 1000])
</div>
