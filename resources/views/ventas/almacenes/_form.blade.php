<div class="row">
    @include('ventas.partials.campo_select', ['name' => 'tienda_id', 'label' => 'Tienda', 'col' => 4, 'value' => $almacen->tienda_id, 'options' => $tiendas, 'required' => true])
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 4, 'value' => $almacen->nombre, 'required' => true, 'maxlength' => 120])
    @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'Código (vacío = automático)', 'col' => 2, 'value' => $almacen->codigo, 'maxlength' => 20, 'placeholder' => 'Automático'])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 2, 'value' => $almacen->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_select', ['name' => 'tipo', 'label' => 'Tipo', 'col' => 4, 'value' => $almacen->tipo ?? 'principal', 'options' => collect(\App\Models\Almacen::TIPOS)->map(fn ($t) => $t[0])->all(), 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_select', ['name' => 'encargado_id', 'label' => 'Encargado', 'col' => 4, 'value' => $almacen->encargado_id, 'options' => $encargados, 'placeholder' => '-- Sin asignar --'])
    @include('ventas.partials.campo_check', ['name' => 'es_principal', 'label' => 'Predeterminado de la tienda', 'col' => 4, 'value' => $almacen->es_principal])
    @include('ventas.partials.campo_texto', ['name' => 'ubicaciones', 'label' => 'Ubicaciones (racks o pasillos)', 'col' => 6, 'value' => $almacen->ubicaciones, 'maxlength' => 1000, 'placeholder' => 'Rack 1-A, Rack 1-B…'])
    @include('ventas.partials.campo_texto', ['name' => 'direccion', 'label' => 'Dirección', 'col' => 6, 'value' => $almacen->direccion, 'maxlength' => 255])
</div>
<small class="text-muted d-block mb-3">El nombre es libre; el tipo define el comportamiento (Merma no se usa para vender). “Tránsito” es automático y no se crea.</small>
