<div class="row">
    @include('ventas.partials.campo_texto', [
        'name' => 'codigo',
        'label' => 'Código de sucursal',
        'col' => 4,
        'value' => $tienda->codigo,
        'required' => true,
        'maxlength' => 20,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'nombre',
        'label' => 'Nombre',
        'col' => 8,
        'value' => $tienda->nombre,
        'required' => true,
        'maxlength' => 120,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'rubro',
        'label' => 'Rubro',
        'col' => 4,
        'value' => $tienda->rubro,
        'maxlength' => 80,
    ])
    @include('ventas.partials.campo_select', [
        'name' => 'ciudad_id',
        'label' => 'Ciudad',
        'col' => 4,
        'value' => $tienda->ciudad_id,
        'options' => $ciudades,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'prefijo_comprobante',
        'label' => 'Prefijo de comprobante',
        'col' => 4,
        'value' => $tienda->prefijo_comprobante,
        'maxlength' => 10,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'telefono',
        'label' => 'Teléfono',
        'col' => 4,
        'value' => $tienda->telefono,
        'maxlength' => 30,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'email',
        'label' => 'Correo',
        'col' => 4,
        'type' => 'email',
        'value' => $tienda->email,
        'maxlength' => 150,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'direccion',
        'label' => 'Dirección',
        'col' => 4,
        'value' => $tienda->direccion,
        'maxlength' => 255,
    ])
    @include('ventas.partials.campo_texto', [
        'name' => 'pie_ticket',
        'label' => 'Pie del ticket',
        'col' => 8,
        'value' => $tienda->pie_ticket,
        'maxlength' => 255,
        'placeholder' => '¡Gracias por su compra!',
    ])
    @include('ventas.partials.campo_select', [
        'name' => 'estado',
        'label' => 'Estado',
        'col' => 2,
        'value' => $tienda->estado ?? 1,
        'options' => [1 => 'Activo', 0 => 'Inactivo'],
        'sinVacio' => true,
        'required' => true,
    ])
    @include('ventas.partials.campo_check', [
        'name' => 'es_matriz',
        'label' => 'Es la tienda matriz',
        'col' => 2,
        'value' => $tienda->es_matriz,
    ])
</div>
