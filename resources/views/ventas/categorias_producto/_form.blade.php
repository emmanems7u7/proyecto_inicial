<div class="row">
    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 6, 'value' => $categoria->nombre, 'required' => true, 'maxlength' => 100])
    @include('ventas.partials.campo_select', ['name' => 'parent_id', 'label' => 'Categoría padre', 'col' => 4, 'value' => $categoria->parent_id, 'options' => $padres, 'placeholder' => '-- Ninguna --'])
    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 2, 'value' => $categoria->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
    @include('ventas.partials.campo_textarea', ['name' => 'descripcion', 'label' => 'Descripción', 'col' => 12, 'value' => $categoria->descripcion, 'maxlength' => 255])
</div>
