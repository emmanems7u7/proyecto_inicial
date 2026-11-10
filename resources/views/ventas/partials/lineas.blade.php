{{--
    Editor de líneas reutilizable.
    Parámetros: $productos (colección), $items (líneas iniciales), $modo (venta|cotizacion|compra|orden|transferencia),
                $stock (opcional [producto_id => [almacen_id => cantidad]]), $almacenSelectId (select de almacén; por defecto almacen_id),
                $descuento (descuento global inicial)
--}}
@php
    $modo = $modo ?? 'venta';
    $filas = old('items', $items ?? []);
    $esCompra = in_array($modo, ['compra', 'orden']);
    $conPrecio = $modo !== 'transferencia';
    $conDescuento = in_array($modo, ['venta', 'cotizacion', 'compra']);
    $conDescGlobal = in_array($modo, ['venta', 'cotizacion', 'compra']);
    $conLote = $modo === 'compra';
    $conStock = ! empty($stock);
    $campoPrecio = $esCompra ? 'costo_unitario' : 'precio_unitario';
    $etqPrecio = $esCompra ? 'Costo unit. (Bs)' : 'Precio unit. (Bs)';
    $selAlmacen = $almacenSelectId ?? 'almacen_id';
    $catalogo = $productos->map(fn ($p) => [
        'id' => $p->id,
        'texto' => $p->codigo . ' - ' . $p->nombre,
        'precio' => (float) ($esCompra ? $p->precio_compra : $p->precio_venta),
        'lotes' => $p->maneja_lotes ? 1 : 0,
        'venc' => $p->maneja_vencimiento ? 1 : 0,
    ])->values();
@endphp

<div class="table-responsive">
    <table class="table table-sm align-items-center small-table">
        <thead>
            <tr>
                <th style="min-width: 260px;">Producto</th>
                @if ($conStock) <th class="text-end">Disp.</th> @endif
                <th style="width: 110px;">Cantidad</th>
                @if ($conPrecio) <th style="width: 130px;">{{ $etqPrecio }}</th> @endif
                @if ($conDescuento) <th style="width: 110px;">Desc. (Bs)</th> @endif
                @if ($conLote) <th style="width: 130px;">Lote</th><th style="width: 140px;">Vence</th> @endif
                @if ($conPrecio) <th class="text-end">Subtotal</th> @endif
                <th></th>
            </tr>
        </thead>
        <tbody id="items_body"></tbody>
    </table>
</div>

<button type="button" class="btn btn-sm btn-outline-primary" id="btn_agregar"><i class="fas fa-plus"></i> Agregar producto</button>
@error('items')
    <div class="text-danger small mt-1">{{ $message }}</div>
@enderror

@if ($conPrecio)
    <div class="row justify-content-end mt-2">
        <div class="col-md-4">
            <table class="table table-sm mb-0">
                <tr><td>Subtotal</td><td class="text-end" id="t_subtotal">0.00</td></tr>
                @if ($conDescGlobal)
                    <tr>
                        <td>Descuento global (Bs)</td>
                        <td><input type="number" step="0.01" min="0" name="descuento" id="descuento" class="form-control form-control-sm text-end" value="{{ old('descuento', $descuento ?? 0) }}"></td>
                    </tr>
                @endif
                <tr><td><strong>Total</strong></td><td class="text-end"><strong id="t_total">0.00</strong></td></tr>
            </table>
        </div>
    </div>
@endif

<script>
(function () {
    const productos = @json($catalogo);
    const stock = @json((object) ($stock ?? []));
    const iniciales = @json(array_values($filas));
    const conPrecio = {{ $conPrecio ? 'true' : 'false' }};
    const conDescuento = {{ $conDescuento ? 'true' : 'false' }};
    const conLote = {{ $conLote ? 'true' : 'false' }};
    const conStock = {{ $conStock ? 'true' : 'false' }};
    const campoPrecio = '{{ $campoPrecio }}';
    const selAlmacen = document.getElementById('{{ $selAlmacen }}');
    const body = document.getElementById('items_body');
    let idx = 0;

    const num = v => parseFloat(v) || 0;
    const esc = s => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
    const val = (d, k, def) => (d[k] !== undefined && d[k] !== null ? d[k] : def);

    function opciones(sel) {
        let h = '<option value="">-- Producto --</option>';
        productos.forEach(p => {
            h += '<option value="' + p.id + '"' + (String(p.id) === String(sel) ? ' selected' : '') + '>' + esc(p.texto) + '</option>';
        });
        return h;
    }

    function productoDe(tr) {
        const v = tr.querySelector('.sel-producto').value;
        return productos.find(x => String(x.id) === String(v));
    }

    function actualizarDisponible(tr) {
        if (!conStock) return;
        const p = productoDe(tr);
        const celda = tr.querySelector('.disp');
        if (!p || !selAlmacen) { celda.textContent = '—'; return; }
        const porAlmacen = stock[p.id];
        const v = porAlmacen ? porAlmacen[selAlmacen.value] : undefined;
        celda.textContent = v !== undefined ? parseFloat(v) : 0;
    }

    function recalcular() {
        let sub = 0;
        body.querySelectorAll('tr').forEach(tr => {
            if (!conPrecio) return;
            const d = tr.querySelector('.inp-desc');
            const s = Math.max(0, num(tr.querySelector('.inp-cant').value) * num(tr.querySelector('.inp-precio').value) - num(d ? d.value : 0));
            tr.querySelector('.sub').textContent = s.toFixed(2);
            sub += s;
        });
        const elSub = document.getElementById('t_subtotal');
        if (elSub) {
            elSub.textContent = sub.toFixed(2);
            const g = document.getElementById('descuento');
            document.getElementById('t_total').textContent = Math.max(0, sub - num(g ? g.value : 0)).toFixed(2);
        }
    }

    function alElegir(tr, cambio) {
        const p = productoDe(tr);
        if (p && cambio && conPrecio) tr.querySelector('.inp-precio').value = p.precio;
        if (conLote) {
            tr.querySelector('.inp-lote').classList.toggle('d-none', !(p && p.lotes));
            tr.querySelector('.inp-venc').classList.toggle('d-none', !(p && p.venc));
        }
        actualizarDisponible(tr);
        recalcular();
    }

    function fila(d) {
        d = d || {};
        const i = idx++;
        const tr = document.createElement('tr');
        let h = '<td><select class="form-select form-select-sm sel-producto" name="items[' + i + '][producto_id]" required>' + opciones(d.producto_id) + '</select></td>';
        if (conStock) h += '<td class="text-end disp text-muted">—</td>';
        h += '<td><input type="number" step="0.001" min="0.001" class="form-control form-control-sm inp-cant" name="items[' + i + '][cantidad]" value="' + val(d, 'cantidad', 1) + '" required></td>';
        if (conPrecio) h += '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm inp-precio" name="items[' + i + '][' + campoPrecio + ']" value="' + val(d, campoPrecio, 0) + '" required></td>';
        if (conDescuento) h += '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm inp-desc" name="items[' + i + '][descuento]" value="' + val(d, 'descuento', 0) + '"></td>';
        if (conLote) {
            h += '<td><input type="text" maxlength="60" class="form-control form-control-sm inp-lote d-none" placeholder="Auto" name="items[' + i + '][numero_lote]" value="' + esc(val(d, 'numero_lote', '')) + '"></td>';
            h += '<td><input type="date" class="form-control form-control-sm inp-venc d-none" name="items[' + i + '][fecha_vencimiento]" value="' + esc(val(d, 'fecha_vencimiento', '')) + '"></td>';
        }
        if (conPrecio) h += '<td class="text-end sub">0.00</td>';
        h += '<td><button type="button" class="btn btn-xs btn-danger mb-0 px-2 py-1 btn-quitar"><i class="fas fa-times"></i></button></td>';

        tr.innerHTML = h;
        body.appendChild(tr);

        tr.querySelector('.sel-producto').addEventListener('change', () => alElegir(tr, true));
        tr.querySelectorAll('input').forEach(inp => inp.addEventListener('input', recalcular));
        tr.querySelector('.btn-quitar').addEventListener('click', () => { tr.remove(); recalcular(); });
        alElegir(tr, false);
    }

    document.getElementById('btn_agregar').addEventListener('click', () => fila({}));
    const g = document.getElementById('descuento');
    if (g) g.addEventListener('input', recalcular);
    if (selAlmacen) selAlmacen.addEventListener('change', () => body.querySelectorAll('tr').forEach(actualizarDisponible));

    (iniciales.length ? iniciales : [{}]).forEach(fila);
})();
</script>
