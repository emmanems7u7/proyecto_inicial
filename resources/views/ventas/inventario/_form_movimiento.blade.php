<div class="row">
    @include('ventas.partials.campo_select', [
        'name' => 'almacen_id', 'label' => 'Almacén', 'col' => 6, 'required' => true,
        'value' => $almacenId, 'options' => $opcionesAlmacen, 'sinVacio' => true,
    ])

    <div class="col-md-6 mb-3">
        <label for="producto_id" class="form-label">Producto <span class="text-danger">*</span></label>
        <select id="producto_id" name="producto_id" class="form-select @error('producto_id') is-invalid @enderror" required>
            <option value="">-- Seleccione --</option>
            @foreach ($productos as $p)
                <option value="{{ $p->id }}"
                        data-lotes="{{ $p->maneja_lotes ? 1 : 0 }}"
                        data-venc="{{ $p->maneja_vencimiento ? 1 : 0 }}"
                        @selected((string) old('producto_id', $productoId) === (string) $p->id)>
                    {{ $p->codigo }} - {{ $p->nombre }}
                </option>
            @endforeach
        </select>
        @error('producto_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    @include('ventas.partials.campo_select', [
        'name' => 'operacion', 'label' => 'Operación', 'col' => 4, 'required' => true,
        'value' => 'entrada', 'sinVacio' => true,
        'options' => [
            'entrada' => 'Entrada (ingreso / stock inicial)',
            'salida' => 'Salida (baja de stock)',
            'ajuste_mas' => 'Ajuste + (sobrante)',
            'ajuste_menos' => 'Ajuste − (faltante)',
        ],
    ])

    @include('ventas.partials.campo_texto', [
        'name' => 'cantidad', 'label' => 'Cantidad', 'col' => 4, 'required' => true,
        'type' => 'number', 'step' => '0.001', 'min' => '0',
    ])

    @include('ventas.partials.campo_select', [
        'name' => 'motivo_id', 'label' => 'Motivo (obligatorio salvo en entradas)', 'col' => 4,
        'options' => $motivos, 'placeholder' => '-- Sin motivo --',
    ])

    <div id="bloque_lote" class="col-md-4 mb-3 d-none">
        <label for="numero_lote" class="form-label">N.º de lote</label>
        <input type="text" id="numero_lote" name="numero_lote" maxlength="60" value="{{ old('numero_lote') }}"
               class="form-control @error('numero_lote') is-invalid @enderror" placeholder="Vacío = se genera automático">
        @error('numero_lote')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div id="bloque_vencimiento" class="col-md-4 mb-3 d-none">
        <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento <span class="text-danger">*</span></label>
        <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}"
               class="form-control @error('fecha_vencimiento') is-invalid @enderror">
        @error('fecha_vencimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div id="bloque_costo" class="col-md-4 mb-3">
        <label for="costo_unitario" class="form-label">Costo unitario (Bs)</label>
        <input type="number" step="0.01" min="0" id="costo_unitario" name="costo_unitario" value="{{ old('costo_unitario') }}"
               class="form-control @error('costo_unitario') is-invalid @enderror" placeholder="Vacío = precio de compra">
        @error('costo_unitario')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div id="aviso_fefo" class="col-12 mb-3 d-none">
        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Este producto se controla por lotes: la salida descuenta primero los lotes que vencen antes (FEFO).</small>
    </div>

    @include('ventas.partials.campo_texto', ['name' => 'observacion', 'label' => 'Observación', 'col' => 12, 'maxlength' => 255])
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const producto = document.getElementById('producto_id');
    const operacion = document.getElementById('operacion');

    function actualizar() {
        const opt = producto.options[producto.selectedIndex];
        const lotes = opt && opt.dataset.lotes === '1';
        const venc = opt && opt.dataset.venc === '1';
        const ingreso = operacion.value === 'entrada' || operacion.value === 'ajuste_mas';

        document.getElementById('bloque_lote').classList.toggle('d-none', !(lotes && ingreso));
        document.getElementById('bloque_vencimiento').classList.toggle('d-none', !(venc && ingreso));
        document.getElementById('bloque_costo').classList.toggle('d-none', !ingreso);
        document.getElementById('aviso_fefo').classList.toggle('d-none', !(lotes && !ingreso));
    }

    producto.addEventListener('change', actualizar);
    operacion.addEventListener('change', actualizar);
    actualizar();
});
</script>
