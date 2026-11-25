{{-- Formulario de producto con catálogo de presentaciones.
     Variables: $producto, $categorias, $unidades, $filasPresentaciones, $catalogoPresentaciones,
                $accion, $metodo (POST|PUT), $cancelar, $conOtro (bool) --}}
@once
<style>
    .pf-card { background:#fff; border:1px solid #eef0f4; border-radius:18px; box-shadow:0 4px 18px rgba(15,23,42,.06); }
    .pf-card-b { padding:1.1rem 1.2rem; }
    .pf-title { display:flex; align-items:center; gap:.6rem; font-size:.95rem; font-weight:700; color:#1f2937; margin:0 0 .9rem; }
    .pf-title .ico { width:30px; height:30px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.8rem; background:linear-gradient(135deg,#4f46e5,#7c3aed); }
    .pf-foto { position:relative; border:2px dashed #c7d2fe; border-radius:16px; background:#f5f7ff; min-height:170px; display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; transition:border-color .15s, background .15s; }
    .pf-foto:hover { border-color:#4f46e5; background:#eef2ff; }
    .pf-foto img { width:100%; height:170px; object-fit:cover; }
    .pf-foto .vacio { text-align:center; color:#6b7280; font-size:.85rem; }
    .pf-foto .vacio i { font-size:1.8rem; color:#a5b4fc; display:block; margin-bottom:.4rem; }
    .pf-tabla { width:100%; border-collapse:separate; border-spacing:0 6px; }
    .pf-tabla th { font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; font-weight:700; padding:0 .35rem; white-space:nowrap; }
    .pf-tabla td { padding:0 .25rem; vertical-align:middle; }
    .pf-tabla input { width:100%; height:36px; border:1.5px solid #e5e7eb; border-radius:10px; padding:0 .55rem; font-size:.82rem; background:#fff; transition:border-color .15s, box-shadow .15s; }
    .pf-tabla input:focus { outline:0; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.12); }
    .pf-tabla input[readonly] { background:#f3f4f6; color:#6b7280; }
    .pf-tabla input.num { text-align:right; }
    .pf-tabla tr.fila { background:#fafbff; }
    .pf-tabla tr.fila td:first-child { border-radius:12px 0 0 12px; padding-left:.5rem; }
    .pf-tabla tr.fila td:last-child { border-radius:0 12px 12px 0; padding-right:.5rem; }
    .pf-base { font-size:.62rem; font-weight:700; background:#e0e7ff; color:#4338ca; border-radius:999px; padding:.1rem .5rem; margin-left:.3rem; vertical-align:middle; }
    .pf-margen { font-weight:700; font-size:.8rem; white-space:nowrap; }
    .pf-margen.ok { color:#059669; } .pf-margen.mal { color:#dc2626; }
    .pf-x { border:0; background:#fee2e2; color:#dc2626; width:28px; height:28px; border-radius:8px; font-size:.7rem; }
    .pf-add { border:1.5px dashed #c7d2fe; background:#f5f7ff; color:#4f46e5; border-radius:12px; padding:.5rem 1rem; font-weight:700; font-size:.82rem; width:100%; transition:background .15s; }
    .pf-add:hover { background:#eef2ff; }
    .pf-btn { border:0; border-radius:12px; padding:.65rem 1.3rem; font-weight:700; color:#fff; background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 8px 18px rgba(16,185,129,.3); }
    .pf-btn.sec { background:#fff; color:#4f46e5; border:1.5px solid #c7d2fe; box-shadow:none; }
    .pf-btn.gris { background:#f3f4f6; color:#4b5563; box-shadow:none; }
</style>
@endonce

@php($uid = 'pf' . ($producto->id ?? 'nuevo') . rand(10, 99))
<form method="POST" action="{{ $accion }}" enctype="multipart/form-data" autocomplete="off" id="{{ $uid }}">
    @csrf
    @if ($metodo === 'PUT') @method('PUT') @endif

    <div class="row g-3">
        {{-- Columna izquierda: foto + control --}}
        <div class="col-lg-3">
            <div class="pf-card mb-3"><div class="pf-card-b">
                <h6 class="pf-title"><span class="ico"><i class="fas fa-image"></i></span> Foto del producto</h6>
                <label class="pf-foto w-100 mb-2" for="{{ $uid }}_img">
                    @if ($producto->imagen)
                        <img src="{{ str_starts_with($producto->imagen, 'http') ? $producto->imagen : asset('storage/' . ltrim($producto->imagen, '/')) }}" alt="" id="{{ $uid }}_prev">
                    @else
                        <div class="vacio" id="{{ $uid }}_vacio"><i class="fas fa-cloud-upload-alt"></i>Subir imagen</div>
                        <img src="" alt="" id="{{ $uid }}_prev" style="display:none;">
                    @endif
                </label>
                <input type="file" name="imagen" id="{{ $uid }}_img" accept="image/*" class="d-none">
                @error('imagen')<small class="text-danger d-block">{{ $message }}</small>@enderror
                @if ($producto->imagen)
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="quitar_imagen" value="1" id="{{ $uid }}_q"><label class="form-check-label small" for="{{ $uid }}_q">Quitar la foto actual</label></div>
                @endif
                <small class="text-muted">JPG o PNG, máximo 2 MB.</small>
            </div></div>

            <div class="pf-card"><div class="pf-card-b">
                <h6 class="pf-title"><span class="ico"><i class="fas fa-hourglass-half"></i></span> Control</h6>
                <div class="form-check form-switch mb-1">
                    <input type="hidden" name="maneja_vencimiento" value="0">
                    <input class="form-check-input" type="checkbox" id="{{ $uid }}_venc" name="maneja_vencimiento" value="1" @checked(old('maneja_vencimiento', $producto->maneja_vencimiento))>
                    <label class="form-check-label fw-bold" for="{{ $uid }}_venc">Controlar vencimiento</label>
                </div>
                <small class="text-muted d-block mb-2">Si se activa, las compras piden lote y fecha de vencimiento.</small>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="maneja_lotes" value="0">
                    <input class="form-check-input" type="checkbox" id="{{ $uid }}_lotes" name="maneja_lotes" value="1" @checked(old('maneja_lotes', $producto->maneja_lotes))>
                    <label class="form-check-label" for="{{ $uid }}_lotes">Controlar por lotes (sin vencimiento)</label>
                </div>
                <label class="form-label small fw-bold text-muted text-uppercase" for="{{ $uid }}_min">Stock mínimo</label>
                <div class="input-group input-group-sm">
                    <input type="number" step="any" min="0" class="form-control" id="{{ $uid }}_min" name="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo ?? 0) }}">
                    <span class="input-group-text">unid.</span>
                </div>
                <small class="text-muted">Valor por defecto para cada almacén; luego se ajusta por almacén en Inventario.</small>
            </div></div>
        </div>

        {{-- Columna derecha: datos + presentaciones --}}
        <div class="col-lg-9">
            <div class="pf-card mb-3"><div class="pf-card-b">
                <h6 class="pf-title"><span class="ico"><i class="fas fa-box"></i></span> Datos del producto</h6>
                <div class="row">
                    @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 4, 'value' => $producto->nombre, 'required' => true, 'maxlength' => 180])
                    @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'SKU / Código', 'col' => 4, 'value' => $producto->codigo, 'required' => true, 'maxlength' => 50])
                    @include('ventas.partials.campo_texto', ['name' => 'codigo_barras', 'label' => 'Código de barras', 'col' => 4, 'value' => $producto->codigo_barras, 'maxlength' => 60, 'placeholder' => 'Escanear o escribir'])
                    @include('ventas.partials.campo_select', ['name' => 'categoria_producto_id', 'label' => 'Categoría', 'col' => 4, 'value' => $producto->categoria_producto_id, 'options' => $categorias])
                    @include('ventas.partials.campo_texto', ['name' => 'marca', 'label' => 'Marca', 'col' => 4, 'value' => $producto->marca, 'maxlength' => 80])
                    @include('ventas.partials.campo_select', ['name' => 'estado', 'label' => 'Estado', 'col' => 4, 'value' => $producto->estado ?? 1, 'options' => [1 => 'Activo', 0 => 'Inactivo'], 'sinVacio' => true, 'required' => true])
                    @include('ventas.partials.campo_select', ['name' => 'unidad_medida_id', 'label' => 'Unidad de medida base', 'col' => 4, 'value' => $producto->unidad_medida_id, 'options' => $unidades, 'required' => true])
                    @include('ventas.partials.campo_texto', ['name' => 'descripcion', 'label' => 'Descripción', 'col' => 8, 'value' => $producto->descripcion, 'maxlength' => 2000])
                </div>
            </div></div>

            <div class="pf-card"><div class="pf-card-b">
                <h6 class="pf-title"><span class="ico"><i class="fas fa-cubes"></i></span> Presentaciones y precios
                    <small class="text-muted fw-normal ms-1">la unidad es la base del stock</small></h6>
                @error('presentaciones')<div class="alert alert-danger text-white py-2">{{ $message }}</div>@enderror
                @foreach ($errors->get('presentaciones.*') as $msgs)<small class="text-danger d-block">{{ $msgs[0] }}</small>@break @endforeach

                <div class="table-responsive">
                    <table class="pf-tabla">
                        <thead><tr>
                            <th style="min-width:150px">Presentación</th><th style="width:80px">Unid.</th><th style="width:90px">Costo</th>
                            <th style="width:90px">Menor</th><th style="width:90px">Mayor.</th><th style="width:90px">Distrib.</th>
                            <th style="width:70px">Margen</th><th style="min-width:130px">Nombre popular</th><th style="width:34px"></th>
                        </tr></thead>
                        <tbody class="pf-filas"></tbody>
                    </table>
                </div>
                <datalist id="{{ $uid }}_lista">
                    @foreach ($catalogoPresentaciones as $nombre => $unid)<option value="{{ $nombre }}" data-u="{{ $unid + 0 }}">@endforeach
                </datalist>
                <button type="button" class="pf-add mt-1 pf-agregar"><i class="fas fa-plus me-1"></i> Agregar presentación <small class="fw-normal text-muted">(solo las que existan para este producto)</small></button>
                <small class="text-muted d-block mt-2">Elige del catálogo (Unidad, Media caja, Caja…) o escribe una nueva: se guardará en el catálogo. Cada producto define cuántas unidades trae y sus precios. El <strong>margen</strong> se calcula sobre el costo. El nombre popular (por ejemplo «jaba») sirve para encontrar el producto al buscar.</small>
            </div></div>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ $cancelar }}" class="pf-btn gris text-decoration-none">Cancelar</a>
        <button type="submit" class="pf-btn"><i class="fas fa-save me-1"></i> Guardar producto</button>
        @if ($conOtro)
            <button type="submit" name="agregar_otro" value="1" class="pf-btn sec">Guardar y agregar otro</button>
        @endif
    </div>
</form>

<script>
(function () {
    const form = document.getElementById(@json($uid));
    const FILAS = @json(array_values($filasPresentaciones));
    const cuerpo = form.querySelector('.pf-filas');
    const lista = document.getElementById(@json($uid . '_lista'));
    const CAT = {}; lista.querySelectorAll('option').forEach(o => CAT[o.value.toLowerCase()] = parseFloat(o.dataset.u));
    let n = 0;
    const num = v => { const x = parseFloat(v); return isNaN(x) ? 0 : x; };
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const campos = ['precio_costo', 'precio_menor', 'precio_mayor', 'precio_distribuidor'];

    function fila(f, base) {
        const i = n++;
        const tr = document.createElement('tr');
        tr.className = 'fila';
        const ro = base ? 'readonly' : '';
        const val = k => (f && f[k] != null && f[k] !== '') ? f[k] : '';
        tr.innerHTML = `
            <td>${f && f.id ? `<input type="hidden" name="presentaciones[${i}][id]" value="${f.id}">` : ''}
                <input type="text" name="presentaciones[${i}][nombre]" value="${esc(val('nombre'))}" ${base ? '' : 'list="' + lista.id + '"'} placeholder="Ej. Caja" required data-k="nombre">${base ? '<span class="pf-base">base</span>' : ''}</td>
            <td><input class="num" type="number" step="any" min="0.001" name="presentaciones[${i}][unidades]" value="${base ? 1 : val('unidades')}" ${ro} required data-k="unidades"></td>
            ${campos.map(c => `<td><input class="num" type="number" step="0.01" min="0" name="presentaciones[${i}][${c}]" value="${val(c)}" required data-k="${c}"></td>`).join('')}
            <td><span class="pf-margen" data-m>—</span></td>
            <td><input type="text" name="presentaciones[${i}][nombre_popular]" value="${esc(val('nombre_popular'))}" maxlength="120" placeholder="Opcional" data-k="nombre_popular"></td>
            <td>${base ? '' : '<button type="button" class="pf-x" title="Quitar"><i class="fas fa-times"></i></button>'}</td>`;
        cuerpo.appendChild(tr);
        margen(tr);
        return tr;
    }

    function margen(tr) {
        const c = num(tr.querySelector('[data-k=precio_costo]').value), m = num(tr.querySelector('[data-k=precio_menor]').value);
        const el = tr.querySelector('[data-m]');
        if (c > 0 && m > 0) { const p = (m - c) / c * 100; el.textContent = Math.round(p) + '%'; el.className = 'pf-margen ' + (p >= 0 ? 'ok' : 'mal'); }
        else { el.textContent = '—'; el.className = 'pf-margen'; }
    }

    // Sugiere costo y precios = base × unidades mientras el usuario no los haya escrito.
    function sugerir(tr) {
        const base = cuerpo.firstElementChild, u = num(tr.querySelector('[data-k=unidades]').value);
        if (!u || tr === base) return;
        campos.forEach(c => {
            const inp = tr.querySelector(`[data-k=${c}]`), b = num(base.querySelector(`[data-k=${c}]`).value);
            if (b > 0 && (inp.value === '' || inp.dataset.auto === '1')) { inp.value = (b * u).toFixed(2); inp.dataset.auto = '1'; }
        });
        margen(tr);
    }

    cuerpo.addEventListener('input', e => {
        const tr = e.target.closest('tr'); if (!tr) return;
        const k = e.target.dataset.k;
        if (campos.includes(k)) delete e.target.dataset.auto;
        if (k === 'nombre' && tr !== cuerpo.firstElementChild) {
            const u = CAT[e.target.value.trim().toLowerCase()], uni = tr.querySelector('[data-k=unidades]');
            if (u && (uni.value === '' || uni.dataset.auto === '1')) { uni.value = u; uni.dataset.auto = '1'; sugerir(tr); }
        }
        if (k === 'unidades') { delete e.target.dataset.auto; sugerir(tr); }
        if (tr === cuerpo.firstElementChild && campos.includes(k)) { [...cuerpo.children].slice(1).forEach(sugerir); }
        margen(tr);
    });
    cuerpo.addEventListener('click', e => { const b = e.target.closest('.pf-x'); if (b) b.closest('tr').remove(); });
    form.querySelector('.pf-agregar').addEventListener('click', () => { const tr = fila({}, false); tr.querySelector('[data-k=nombre]').focus(); });

    FILAS.forEach((f, k) => fila(f, k === 0));

    // Vista previa de la foto
    const inp = document.getElementById(@json($uid . '_img')), prev = document.getElementById(@json($uid . '_prev')), vac = document.getElementById(@json($uid . '_vacio'));
    inp.addEventListener('change', () => {
        const f = inp.files[0]; if (!f) return;
        const r = new FileReader(); r.onload = ev => { prev.src = ev.target.result; prev.style.display = 'block'; if (vac) vac.style.display = 'none'; }; r.readAsDataURL(f);
    });
    // Controlar vencimiento implica lotes
    const venc = document.getElementById(@json($uid . '_venc')), lotes = document.getElementById(@json($uid . '_lotes'));
    venc.addEventListener('change', () => { if (venc.checked) lotes.checked = true; });
})();
</script>
