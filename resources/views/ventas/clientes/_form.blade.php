<div class="row">
    @include('ventas.partials.campo_select', ['name' => 'tipo_documento_id', 'label' => 'Tipo de documento', 'col' => 4, 'value' => $cliente->tipo_documento_id, 'options' => $tiposDocumento])
    @include('ventas.partials.campo_texto', ['name' => 'numero_documento', 'label' => 'Número de documento', 'col' => 5, 'value' => $cliente->numero_documento, 'maxlength' => 30])
    @include('ventas.partials.campo_texto', ['name' => 'complemento', 'label' => 'Complemento', 'col' => 3, 'value' => $cliente->complemento, 'maxlength' => 5])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre / Razón social', 'col' => 8, 'value' => $cliente->nombre, 'required' => true, 'maxlength' => 180])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 4, 'value' => $cliente->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'telefono', 'label' => 'Teléfono', 'col' => 4, 'value' => $cliente->telefono, 'maxlength' => 30])
    @include('ventas.partials.campo_texto', ['name' => 'email', 'label' => 'Correo', 'col' => 4, 'type' => 'email', 'value' => $cliente->email, 'maxlength' => 150])
    @include('ventas.partials.campo_select', ['name' => 'ciudad_id', 'label' => 'Ciudad', 'col' => 4, 'value' => $cliente->ciudad_id, 'options' => $ciudades])
    @include('ventas.partials.campo_texto', ['name' => 'direccion', 'label' => 'Dirección', 'col' => 12, 'value' => $cliente->direccion, 'maxlength' => 255])
    @include('ventas.partials.campo_texto', ['name' => 'limite_credito', 'label' => 'Límite de crédito (Bs)', 'col' => 4, 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => $cliente->limite_credito, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'dias_credito', 'label' => 'Días de crédito', 'col' => 4, 'type' => 'number', 'step' => '1', 'min' => '0', 'value' => $cliente->dias_credito, 'required' => true])
    <div class="col-md-4"></div>
    @include('ventas.partials.campo_textarea', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'value' => $cliente->observaciones, 'maxlength' => 1000])
</div>
