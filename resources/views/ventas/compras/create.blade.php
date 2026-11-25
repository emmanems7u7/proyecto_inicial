@extends('layouts.argon')

@section('content')
<style>
    .cp-hero { position:relative; overflow:hidden; border-radius:18px; padding:1.1rem 1.4rem; color:#fff; background:linear-gradient(120deg,#0ea5e9 0%,#4f46e5 60%,#7c3aed 100%); box-shadow:0 10px 28px rgba(79,70,229,.25); }
    .cp-hero::before { content:""; position:absolute; width:220px; height:220px; right:-60px; top:-90px; border-radius:50%; background:rgba(255,255,255,.12); }
    .cp-hero h1 { font-size:1.4rem; font-weight:800; color:#fff; margin:0; position:relative; }
    .cp-hero .sub { font-size:.85rem; opacity:.9; position:relative; }
    .cp-panel { background:#fff; border:1px solid #eef0f4; border-radius:18px; box-shadow:0 4px 18px rgba(15,23,42,.06); }
    .cp-title { display:flex; align-items:center; gap:.6rem; font-size:.95rem; font-weight:700; color:#1f2937; margin:0 0 .9rem; }
    .cp-title .ico { width:30px; height:30px; border-radius:9px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.8rem; background:linear-gradient(135deg,#4f46e5,#7c3aed); }
    .cp-tabla { width:100%; border-collapse:separate; border-spacing:0 8px; }
    .cp-tabla th { font-size:.68rem; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; font-weight:700; padding:0 .5rem; white-space:nowrap; }
    .cp-tabla td { padding:.55rem .5rem; vertical-align:middle; background:#fafbff; }
    .cp-tabla td:first-child { border-radius:14px 0 0 14px; }
    .cp-tabla td:last-child { border-radius:0 14px 14px 0; }
    .cp-tabla input { height:36px; border:1.5px solid #e5e7eb; border-radius:10px; padding:0 .55rem; font-size:.84rem; background:#fff; width:100%; transition:border-color .15s, box-shadow .15s; }
    .cp-tabla input:focus { outline:0; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,.12); }
    .cp-tabla input.num { text-align:right; font-weight:700; }
    .cp-th { width:42px; height:42px; border-radius:11px; object-fit:cover; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:.7rem; color:rgba(31,41,55,.85); flex-shrink:0; }
    .cp-pres { display:inline-flex; background:#eef0f7; border-radius:10px; padding:3px; gap:2px; }
    .cp-pres button { border:0; background:transparent; padding:.22rem .6rem; font-size:.72rem; font-weight:600; border-radius:8px; color:#4b5563; }
    .cp-pres button.on { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 3px 8px rgba(79,70,229,.3); }
    .cp-sub { font-size:.72rem; color:#6b7280; margin-top:.15rem; }
    .cp-na { font-size:.7rem; background:#eef0f7; color:#6b7280; border-radius:999px; padding:.2rem .7rem; }
    .cp-x { border:0; background:#fee2e2; color:#dc2626; width:28px; height:28px; border-radius:8px; font-size:.7rem; }
    .cp-add { position:relative; }
    .cp-add i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#9ca3af; }
    .cp-add input { width:100%; height:44px; border:1.5px dashed #c7d2fe; background:#f5f7ff; border-radius:12px; padding:0 14px 0 38px; font-size:.9rem; }
    .cp-add input:focus { outline:0; border-style:solid; border-color:#4f46e5; background:#fff; box-shadow:0 0 0 4px rgba(79,70,229,.12); }
    .cp-box { background:#f8f9fc; border:1px solid #eef0f4; border-radius:14px; padding:.9rem 1rem; height:100%; }
    .cp-box h6 { font-weight:800; font-size:.88rem; margin-bottom:.5rem; }
    .cp-box .li { font-size:.82rem; color:#4b5563; padding:.15rem 0; }
    .cp-box .li b { color:#111827; }
    .cp-gan.ok { color:#059669; font-weight:700; } .cp-gan.mal { color:#dc2626; font-weight:700; }
    .cp-total { font-size:1.7rem; font-weight:800; color:#111827; }
    .cp-btn { border:0; border-radius:14px; padding:.8rem 1.6rem; font-weight:800; color:#fff; background:linear-gradient(135deg,#10b981,#059669); box-shadow:0 10px 22px rgba(16,185,129,.35); }
    .cp-btn:disabled { opacity:.6; }
    .cp-vacio { text-align:center; color:#9ca3af; padding:1.6rem 1rem; }
    .cp-vacio i { font-size:2rem; color:#c7d2fe; display:block; margin-bottom:.4rem; }
</style>

<div class="cp-hero mb-3">
    <h1><i class="fas fa-truck-loading me-2"></i>Registrar compra</h1>
    <div class="sub"><i class="fas fa-store me-1"></i>{{ $tienda->nombre }}
        @if ($orden) · <i class="fas fa-file-alt me-1"></i>Recepción de la orden {{ $orden->numero }} (se sugieren las cantidades pendientes)@endif</div>
</div>

@include('ventas.partials.alertas')

<form method="POST" action="{{ route('compras.store') }}" autocomplete="off" id="form_compra">
    @csrf
    @if ($orden) <input type="hidden" name="orden_compra_id" value="{{ $orden->id }}"> @endif

    <div class="cp-panel mb-3"><div class="p-3">
        <div class="row">
            @include('ventas.partials.campo_select', ['name' => 'proveedor_id', 'label' => 'Proveedor', 'col' => 4, 'required' => true, 'value' => $proveedorId, 'options' => $proveedores])
            @include('ventas.partials.campo_select', ['name' => 'almacen_id', 'label' => 'Entra a (almacén)', 'col' => 3, 'required' => true, 'sinVacio' => true, 'value' => $almacenId, 'options' => $opcionesAlmacen])
            @include('ventas.partials.campo_texto', ['name' => 'numero_documento_proveedor', 'label' => 'N.º factura / nota del proveedor', 'col' => 3, 'maxlength' => 40])
            @include('ventas.partials.campo_select', ['name' => 'tipo_pago', 'label' => 'Pago', 'col' => 2, 'required' => true, 'sinVacio' => true, 'value' => 'contado', 'options' => ['contado' => 'Contado', 'credito' => 'Crédito']])
        </div>
        <div class="row" id="bloque_contado">
            @include('ventas.partials.campo_check', ['name' => 'pagar_caja', 'label' => 'Registrar el pago como egreso de mi caja abierta', 'col' => 6, 'value' => false])
            @include('ventas.partials.campo_select', ['name' => 'metodo_pago_id', 'label' => 'Método de pago', 'col' => 3, 'value' => $efectivoId, 'options' => $metodos, 'placeholder' => '-- Seleccione --'])
            @unless ($sesion)
                <div class="col-md-3 mb-3 d-flex align-items-end"><small class="text-muted">No tienes caja abierta.</small></div>
            @endunless
        </div>
    </div></div>

    <div class="cp-panel mb-3"><div class="p-3">
        <h6 class="cp-title"><span class="ico"><i class="fas fa-boxes"></i></span> Productos de la compra</h6>
        <div class="table-responsive">
            <table class="cp-tabla">
                <thead><tr>
                    <th style="min-width:190px">Producto</th><th>Presentación</th><th style="width:110px">Cant.</th><th style="width:120px">Costo</th>
                    <th style="width:120px">Lote</th><th style="width:140px">Vence</th><th class="text-end" style="width:100px">Subtotal</th><th style="width:36px"></th>
                </tr></thead>
                <tbody id="lineas"></tbody>
            </table>
        </div>
        <div id="vacio" class="cp-vacio"><i class="fas fa-dolly-flatbed"></i>Aún no agregaste productos a la compra.</div>
        <div class="cp-add mt-2">
            <i class="fas fa-plus"></i>
            <input type="text" id="add_prod" list="lista_prod" placeholder="Agregar producto (nombre, código o código de barras)…">
            <datalist id="lista_prod"></datalist>
        </div>
    </div></div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="cp-box">
                <h6 id="cb_titulo">Costo del producto</h6>
                <div id="cb_info" class="li text-muted">Agrega un producto para ver cómo cambia su costo y su ganancia.</div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="actualizar_costo" value="1" id="actualizar_costo" checked>
                    <label class="form-check-label small" for="actualizar_costo">Actualizar costo del producto en el catálogo</label>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="cp-box">
                <h6>Al registrar la compra</h6>
                <div id="resumen"></div>
            </div>
        </div>
    </div>

    <div class="cp-panel mb-3"><div class="p-3">
        <div class="row">
            @include('ventas.partials.campo_texto', ['name' => 'observaciones', 'label' => 'Observaciones', 'col' => 12, 'maxlength' => 1000])
        </div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex gap-2">
                <a href="{{ route('compras.index') }}" class="btn btn-secondary mb-0">Cancelar</a>
                <button type="submit" class="cp-btn" id="btn_reg"><i class="fas fa-check me-1"></i> Registrar compra</button>
            </div>
            <div class="text-end"><small class="text-muted d-block">Total</small><span class="cp-total" id="total">Bs 0,00</span></div>
        </div>
    </div></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const CAT = @json($catalogo);
    const PROV = @json($proveedoresJs);   // { proveedor_id: días de crédito }
    const ALM = @json($almacenesJs);
    const ITEMS = @json($items);
    const COLORES = ['#F7D56E', '#D9C7A0', '#E8B44F', '#F0C987', '#A8D5A2', '#C9B6E4', '#92C3E4'];
    const $ = id => document.getElementById(id);
    const fmt = n => Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const porId = {}; CAT.forEach(p => porId[p.id] = p);
    const etiqueta = p => p.codigo + ' · ' + p.nombre;
    let lineas = [];   // { pid, pi, qty, costo, lote, vence }
    let ultimo = -1;

    $('lista_prod').innerHTML = CAT.map(p => `<option value="${esc(etiqueta(p))}"></option>`).join('');

    const thumb = p => p.imagen
        ? `<img class="cp-th" src="${esc(p.imagen)}" alt="">`
        : `<span class="cp-th" style="background:linear-gradient(135deg,${COLORES[p.id % 7]},${COLORES[(p.id + 3) % 7]})">${esc(p.nombre.substring(0, 3).toUpperCase())}</span>`;

    function agregar(p, init) {
        init = init || {};
        lineas.push({ pid: p.id, pi: init.pi || 0, qty: init.qty || 1, costo: init.costo != null ? init.costo : p.presentaciones[init.pi || 0].costo, lote: '', vence: '' });
        ultimo = lineas.length - 1;
        pintar();
    }

    function pintar() {
        $('vacio').style.display = lineas.length ? 'none' : '';
        $('lineas').innerHTML = lineas.map((l, i) => {
            const p = porId[l.pid], pres = p.presentaciones[l.pi];
            return `<tr data-i="${i}">
                <td><div class="d-flex align-items-center gap-2">${thumb(p)}<div><div class="fw-bold small">${esc(p.nombre)}</div><div class="text-muted" style="font-size:.7rem">${esc(p.codigo)}</div></div></div>
                    <input type="hidden" name="items[${i}][producto_id]" value="${p.id}">${pres.id ? `<input type="hidden" name="items[${i}][presentacion_id]" value="${pres.id}">` : ''}</td>
                <td><div class="cp-pres">${p.presentaciones.map((x, k) => `<button type="button" class="${k === l.pi ? 'on' : ''}" data-pres="${i}:${k}">${esc(x.nombre)}</button>`).join('')}</div></td>
                <td><input class="num" type="number" step="any" min="0.001" name="items[${i}][cantidad]" value="${l.qty}" data-f="qty" required><div class="cp-sub" data-eq>= ${l.qty * pres.unidades} unid.</div></td>
                <td><input class="num" type="number" step="0.01" min="0" name="items[${i}][costo_unitario]" value="${l.costo}" data-f="costo" required><div class="cp-sub" data-un>${fmt(l.costo / pres.unidades)} por unid.</div></td>
                <td>${p.lotes ? `<input type="text" name="items[${i}][numero_lote]" value="${esc(l.lote)}" maxlength="60" data-f="lote" placeholder="Lote">` : '<span class="cp-na">No aplica</span>'}</td>
                <td>${p.vence ? `<input type="date" name="items[${i}][fecha_vencimiento]" value="${esc(l.vence)}" data-f="vence">` : '<span class="cp-na">No aplica</span>'}</td>
                <td class="text-end fw-bold" data-sub>${fmt(l.qty * l.costo)}</td>
                <td><button type="button" class="cp-x" data-quitar="${i}" title="Quitar"><i class="fas fa-times"></i></button></td></tr>`;
        }).join('');
        resumen();
    }

    function resumen() {
        let total = 0;
        lineas.forEach(l => total += l.qty * l.costo);
        $('total').textContent = 'Bs ' + fmt(total);

        // Cuadro de costo (última línea tocada)
        const l = lineas[ultimo];
        if (l) {
            const p = porId[l.pid], pres = p.presentaciones[l.pi];
            const gan = pres.menor - l.costo, pct = l.costo > 0 ? gan / l.costo * 100 : 0;
            $('cb_titulo').textContent = p.nombre + ' · ' + pres.nombre;
            $('cb_info').className = 'li';
            $('cb_info').innerHTML = `Costo anterior <b>${fmt(pres.costo)}</b> → nuevo <b>${fmt(l.costo)}</b><br>`
                + `Precio de venta menor <b>${fmt(pres.menor)}</b> · ganancia <span class="cp-gan ${gan >= 0 ? 'ok' : 'mal'}">Bs ${fmt(gan)} (${Math.round(pct)}%)</span>`;
        } else {
            $('cb_titulo').textContent = 'Costo del producto';
            $('cb_info').className = 'li text-muted';
            $('cb_info').textContent = 'Agrega un producto para ver cómo cambia su costo y su ganancia.';
        }

        // Resumen de efectos
        const alm = ALM.find(a => String(a.id) === $('almacen_id').value);
        const stock = lineas.map(l => { const p = porId[l.pid]; return `+${l.qty * p.presentaciones[l.pi].unidades} ${esc(p.nombre)}`; });
        const lotes = lineas.filter(l => porId[l.pid].lotes && l.lote).map(l => `Lote ${esc(l.lote)} creado${l.vence ? ', vence ' + l.vence.split('-').reverse().slice(1).join('/') : ''}`);
        const credito = $('tipo_pago').value === 'credito';
        const dias = PROV[$('proveedor_id').value] || 30;
        $('resumen').innerHTML =
            `<div class="li">Almacén destino: <b>${alm ? esc(alm.nombre) + ' (' + esc(alm.codigo) + ')' : '—'}</b></div>`
            + `<div class="li">Stock: ${stock.length ? stock.join(' y ') + ' (unidades)' : '—'}</div>`
            + lotes.map(t => `<div class="li">${t}</div>`).join('')
            + (credito ? `<div class="li">Cuenta por pagar: <b>Bs ${fmt(total)}</b> a ${dias} días</div>` : `<div class="li">Pago al contado: <b>Bs ${fmt(total)}</b></div>`)
            + `<div class="li">Movimientos guardados en el kardex</div>`;
    }

    // Actualiza solo los textos calculados de una fila (sin perder el foco al escribir)
    function fila(i) {
        const tr = $('lineas').querySelector(`tr[data-i="${i}"]`), l = lineas[i], pres = porId[l.pid].presentaciones[l.pi];
        tr.querySelector('[data-eq]').textContent = '= ' + (l.qty * pres.unidades) + ' unid.';
        tr.querySelector('[data-un]').textContent = fmt(l.costo / pres.unidades) + ' por unid.';
        tr.querySelector('[data-sub]').textContent = fmt(l.qty * l.costo);
    }

    // ---------- Eventos ----------
    $('lineas').addEventListener('input', e => {
        const tr = e.target.closest('tr'); if (!tr || !e.target.dataset.f) return;
        const i = Number(tr.dataset.i), f = e.target.dataset.f;
        lineas[i][f] = (f === 'qty' || f === 'costo') ? (parseFloat(e.target.value) || 0) : e.target.value;
        ultimo = i; fila(i); resumen();
    });
    $('lineas').addEventListener('click', e => {
        const b = e.target.closest('button'); if (!b) return;
        if (b.dataset.pres !== undefined) {
            const [i, k] = b.dataset.pres.split(':').map(Number);
            lineas[i].pi = k; lineas[i].costo = porId[lineas[i].pid].presentaciones[k].costo; ultimo = i; pintar();
        } else if (b.dataset.quitar !== undefined) {
            lineas.splice(Number(b.dataset.quitar), 1); ultimo = lineas.length - 1; pintar();
        }
    });
    function agregarDesdeBuscador() {
        const v = $('add_prod').value.trim().toLowerCase(); if (!v) return;
        const p = CAT.find(x => etiqueta(x).toLowerCase() === v || (x.barras || '').toLowerCase() === v || x.codigo.toLowerCase() === v)
            || (CAT.filter(x => etiqueta(x).toLowerCase().includes(v)).length === 1 ? CAT.find(x => etiqueta(x).toLowerCase().includes(v)) : null);
        if (p) { agregar(p); $('add_prod').value = ''; }
    }
    $('add_prod').addEventListener('change', agregarDesdeBuscador);
    $('add_prod').addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); agregarDesdeBuscador(); } });

    const tipo = $('tipo_pago'), bloque = $('bloque_contado');
    const alternar = () => { bloque.style.display = tipo.value === 'credito' ? 'none' : ''; resumen(); };
    tipo.addEventListener('change', alternar);
    $('almacen_id').addEventListener('change', resumen);
    $('proveedor_id').addEventListener('change', resumen);

    $('form_compra').addEventListener('submit', e => {
        if (!lineas.length) { e.preventDefault(); alert('Agrega al menos un producto.'); return; }
        const falta = lineas.find(l => { const p = porId[l.pid]; return (p.lotes && !l.lote.trim()) || (p.vence && !l.vence); });
        if (falta) { e.preventDefault(); const p = porId[falta.pid]; alert('«' + p.nombre + '» necesita ' + (p.vence ? 'lote y fecha de vencimiento.' : 'número de lote.')); return; }
        $('btn_reg').disabled = true;
    });

    // Líneas iniciales (recepción de una orden de compra): se cargan en la presentación base
    ITEMS.forEach(it => { const p = porId[it.producto_id]; if (p) agregar(p, { pi: 0, qty: it.cantidad / p.presentaciones[0].unidades, costo: it.costo_unitario * p.presentaciones[0].unidades }); });
    ultimo = lineas.length - 1;
    alternar(); pintar();
});
</script>
@endsection
