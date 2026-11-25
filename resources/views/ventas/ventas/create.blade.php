@extends('layouts.argon')

@section('content')

    <style>
        :root {
            --vt-a: #4f46e5;
            --vt-b: #7c3aed;
            --vt-ink: #1f2937;
            --vt-mut: #6b7280;
            --vt-line: #eef0f4;
        }

        /* ---------- Cabecera ---------- */
        .vt-hero {
            position: relative;
            overflow: hidden;
            border-radius: 18px;
            padding: 1.1rem 1.4rem;
            color: #fff;
            background: linear-gradient(120deg, #4f46e5 0%, #7c3aed 55%, #a855f7 100%);
            box-shadow: 0 10px 28px rgba(79, 70, 229, .25);
            animation: vtIn .45s ease both;
        }

        .vt-hero::before,
        .vt-hero::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
        }

        .vt-hero::before {
            width: 220px;
            height: 220px;
            right: -60px;
            top: -90px;
        }

        .vt-hero::after {
            width: 140px;
            height: 140px;
            right: 130px;
            bottom: -80px;
            background: rgba(255, 255, 255, .08);
        }

        .vt-hero h1 {
            font-size: 1.45rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
            position: relative;
        }

        .vt-hero .sub {
            font-size: .85rem;
            opacity: .88;
            position: relative;
        }

        .vt-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            backdrop-filter: blur(6px);
            font-size: .82rem;
            color: #fff;
            text-decoration: none;
        }

        .vt-pill:hover {
            color: #fff;
            background: rgba(255, 255, 255, .28);
        }

        .vt-live {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #34d399;
            box-shadow: 0 0 0 0 rgba(52, 211, 153, .7);
            animation: vtPulse 1.8s infinite;
        }

        .vt-live.off {
            background: #fbbf24;
            animation: none;
        }

        @keyframes vtPulse {
            70% {
                box-shadow: 0 0 0 9px rgba(52, 211, 153, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(52, 211, 153, 0);
            }
        }

        @keyframes vtIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        /* ---------- Tarjetas base ---------- */
        .vt-panel {
            background: #fff;
            border: 1px solid var(--vt-line);
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
            animation: vtIn .5s ease both;
        }

        .vt-panel-b {
            padding: 1.1rem 1.2rem;
        }

        .vt-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: .95rem;
            font-weight: 700;
            color: var(--vt-ink);
            margin: 0 0 .85rem;
        }

        .vt-title .ico {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: .8rem;
            background: linear-gradient(135deg, var(--vt-a), var(--vt-b));
        }

        /* ---------- Buscador ---------- */
        .vt-search {
            position: relative;
            margin-bottom: .6rem;
        }

        .vt-search i.lupa {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .vt-search input {
            width: 100%;
            height: 46px;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            padding: 0 40px 0 40px;
            background: #f9fafb;
            font-size: .9rem;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .vt-search input:focus {
            outline: 0;
            background: #fff;
            border-color: var(--vt-a);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, .12);
        }

        .vt-search .limpiar {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: #e5e7eb;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: .7rem;
            color: #4b5563;
            display: none;
        }

        .vt-meta {
            display: flex;
            justify-content: space-between;
            font-size: .75rem;
            color: var(--vt-mut);
            margin-bottom: .6rem;
        }

        /* ---------- Productos ---------- */
        .vt-grid {
            max-height: 66vh;
            overflow-y: auto;
            padding: 2px 4px 4px 2px;
        }

        .vt-grid::-webkit-scrollbar {
            width: 6px;
        }

        .vt-grid::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 6px;
        }

        .vt-card {
            position: relative;
            cursor: pointer;
            height: 100%;
            background: #fff;
            border: 1px solid var(--vt-line);
            border-radius: 16px;
            padding: .55rem;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s;
        }

        .vt-card:hover {
            transform: translateY(-3px);
            border-color: #c7d2fe;
            box-shadow: 0 10px 22px rgba(79, 70, 229, .16);
        }

        .vt-card:active {
            transform: scale(.97);
        }

        .vt-card.agotado {
            opacity: .55;
            filter: grayscale(.4);
        }

        .vt-card.flash {
            animation: vtFlash .5s ease;
        }

        @keyframes vtFlash {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, .6);
            }

            100% {
                box-shadow: 0 0 0 14px rgba(16, 185, 129, 0);
            }
        }

        .vt-img,
        .vt-ini {
            width: 100%;
            height: 92px;
            border-radius: 12px;
            object-fit: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: .03em;
            color: rgba(31, 41, 55, .85);
        }

        .vt-th {
            width: 46px;
            height: 46px;
            flex-shrink: 0;
            border-radius: 12px;
            object-fit: cover;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .75rem;
            color: rgba(31, 41, 55, .85);
        }

        .vt-stock {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: .66rem;
            font-weight: 700;
            padding: .15rem .5rem;
            border-radius: 999px;
            color: #fff;
            background: #10b981;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .15);
        }

        .vt-stock.low {
            background: #f59e0b;
        }

        .vt-stock.none {
            background: #ef4444;
        }

        .vt-pname {
            font-size: .83rem;
            font-weight: 700;
            color: var(--vt-ink);
            margin-top: .45rem;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2em;
        }

        .vt-pcode {
            font-size: .68rem;
            color: #9ca3af;
        }

        .vt-price {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: .25rem;
        }

        .vt-price b {
            font-size: .95rem;
            color: var(--vt-a);
        }

        .vt-price .mas {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .7rem;
            color: #fff;
            background: linear-gradient(135deg, var(--vt-a), var(--vt-b));
            opacity: .9;
        }

        .vt-npres {
            font-size: .65rem;
            color: var(--vt-mut);
        }

        /* ---------- Configuración ---------- */
        .vt-config {
            background: #f8f9fc;
            border: 1px solid var(--vt-line);
            border-radius: 14px;
            padding: .8rem .8rem 0;
            margin-bottom: 1rem;
        }

        .vt-config .form-label,
        .vt-pago .form-label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--vt-mut);
        }

        /* ---------- Carrito ---------- */
        .vt-cart-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .vt-count {
            font-size: .72rem;
            font-weight: 700;
            padding: .15rem .6rem;
            border-radius: 999px;
            background: #eef2ff;
            color: var(--vt-a);
        }

        .vt-line {
            border: 1px solid var(--vt-line);
            border-radius: 14px;
            padding: .7rem .8rem;
            margin-bottom: .6rem;
            background: #fff;
            animation: vtIn .25s ease both;
            transition: border-color .15s, box-shadow .15s;
        }

        .vt-line:hover {
            border-color: #c7d2fe;
            box-shadow: 0 4px 12px rgba(79, 70, 229, .08);
        }

        .vt-line.exceso {
            border-color: #fca5a5;
            background: #fff7f7;
        }

        .vt-l1 {
            display: flex;
            align-items: center;
            gap: .7rem;
        }

        .vt-l1 .nom {
            flex: 1;
            min-width: 0;
        }

        .vt-l1 .nom b {
            display: block;
            font-size: .88rem;
            color: var(--vt-ink);
            line-height: 1.2;
        }

        .vt-l1 .nom small {
            color: #9ca3af;
            font-size: .7rem;
        }

        .vt-l1 .sub {
            font-weight: 800;
            font-size: 1rem;
            color: var(--vt-ink);
            white-space: nowrap;
        }

        .vt-x {
            border: 0;
            background: #fee2e2;
            color: #dc2626;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            font-size: .7rem;
            transition: background .15s;
        }

        .vt-x:hover {
            background: #fecaca;
        }

        .vt-l2 {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem .9rem;
            margin-top: .6rem;
            padding-top: .6rem;
            border-top: 1px dashed var(--vt-line);
        }

        .vt-pres {
            display: inline-flex;
            background: #f1f3f9;
            border-radius: 10px;
            padding: 3px;
            gap: 2px;
        }

        .vt-pres button {
            border: 0;
            background: transparent;
            padding: .22rem .65rem;
            font-size: .72rem;
            font-weight: 600;
            border-radius: 8px;
            color: #4b5563;
            transition: all .15s;
        }

        .vt-pres button.on {
            background: linear-gradient(135deg, var(--vt-a), var(--vt-b));
            color: #fff;
            box-shadow: 0 3px 8px rgba(79, 70, 229, .3);
        }

        .vt-pres.fijo span {
            padding: .22rem .65rem;
            font-size: .72rem;
            color: var(--vt-mut);
        }

        .vt-step {
            display: inline-flex;
            align-items: center;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        .vt-step button {
            border: 0;
            background: #f9fafb;
            width: 30px;
            height: 30px;
            font-weight: 700;
            color: #4b5563;
        }

        .vt-step button:hover {
            background: #eef2ff;
            color: var(--vt-a);
        }

        .vt-step input {
            width: 52px;
            height: 30px;
            border: 0;
            text-align: center;
            font-weight: 700;
            font-size: .85rem;
            -moz-appearance: textfield;
        }

        .vt-step input:focus {
            outline: 0;
            background: #f5f7ff;
        }

        .vt-step input::-webkit-outer-spin-button,
        .vt-step input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .vt-eq {
            font-size: .72rem;
            color: var(--vt-mut);
        }

        .vt-eq.bad {
            color: #dc2626;
            font-weight: 700;
        }

        .vt-unit {
            margin-left: auto;
            font-size: .78rem;
            color: var(--vt-mut);
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .vt-desc {
            border: 1.5px dashed #c7d2fe;
            background: #f5f7ff;
            color: var(--vt-a);
            font-size: .72rem;
            font-weight: 700;
            border-radius: 999px;
            padding: .15rem .65rem;
        }

        .vt-desc.on {
            border-style: solid;
            background: #ecfdf5;
            border-color: #6ee7b7;
            color: #047857;
        }

        .vt-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: #9ca3af;
        }

        .vt-empty i {
            font-size: 2.4rem;
            color: #c7d2fe;
            display: block;
            margin-bottom: .5rem;
        }

        /* ---------- Totales ---------- */
        .vt-totales {
            border-radius: 16px;
            padding: 1rem 1.2rem;
            color: #e5e7eb;
            background: linear-gradient(135deg, #0f172a, #1e293b 60%, #312e81);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .28);
        }

        .vt-totales .fila {
            display: flex;
            justify-content: space-between;
            font-size: .85rem;
            padding: .15rem 0;
            color: #cbd5e1;
        }

        .vt-totales .total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: .5rem;
            padding-top: .6rem;
            border-top: 1px solid rgba(255, 255, 255, .15);
            color: #fff;
        }

        .vt-totales .total span:first-child {
            font-size: .85rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .8;
        }

        .vt-totales .total span:last-child {
            font-size: 1.9rem;
            font-weight: 800;
        }

        /* ---------- Pago ---------- */
        .vt-seg {
            display: flex;
            background: #f1f3f9;
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
        }

        .vt-seg button {
            flex: 1;
            border: 0;
            background: transparent;
            padding: .5rem;
            border-radius: 9px;
            font-weight: 700;
            font-size: .8rem;
            color: #4b5563;
            transition: all .15s;
        }

        .vt-seg button.on {
            background: #fff;
            color: var(--vt-a);
            box-shadow: 0 2px 8px rgba(15, 23, 42, .12);
        }

        .vt-cta {
            border: 0;
            border-radius: 14px;
            padding: .9rem 1rem;
            font-weight: 800;
            font-size: 1rem;
            color: #fff;
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 10px 22px rgba(16, 185, 129, .35);
            transition: transform .15s, filter .15s;
        }

        .vt-cta:hover {
            filter: brightness(1.07);
            transform: translateY(-1px);
        }

        .vt-cta:disabled {
            opacity: .6;
            transform: none;
        }

        .vt-cta.pedido {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            box-shadow: 0 10px 22px rgba(14, 165, 233, .35);
        }

        .vt-cta.credito {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 10px 22px rgba(245, 158, 11, .35);
        }

        .vt-cancel {
            border-radius: 14px;
            padding: .9rem 1.2rem;
            background: #f3f4f6;
            color: #4b5563;
            font-weight: 700;
            border: 0;
        }

        .vt-cancel:hover {
            background: #e5e7eb;
            color: #1f2937;
        }

        @media (prefers-reduced-motion: reduce) {

            .vt-hero,
            .vt-panel,
            .vt-line {
                animation: none;
            }
        }
    </style>

    {{-- ====================== CABECERA ====================== --}}
    <div class="vt-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h1><i class="fas fa-cash-register me-2"></i>Nueva venta</h1>
                <div class="sub">
                    <i class="fas fa-store me-1"></i>{{ $tienda->nombre }}
                    · {{ now()->translatedFormat('l d \d\e F') }}
                    · {{ optional(auth()->user())->nombre_completo }}
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if ($cotizacion)
                    <span class="vt-pill"><i class="fas fa-file-invoice"></i> Cotización
                        <strong>{{ $cotizacion->numero }}</strong></span>
                @endif
                @if ($sesion)
                    <a class="vt-pill" href="{{ route('caja.sesiones.show', $sesion) }}"><span class="vt-live"></span>
                        Caja <strong>{{ $sesion->caja->nombre }}</strong> abierta</a>
                @else
                    @can('caja.abrir')
                        <a class="vt-pill" href="{{ route('caja.abrir.create') }}"><span class="vt-live off"></span> Sin caja
                            abierta · <u>Abrir caja</u></a>
                    @else
                        <span class="vt-pill"><span class="vt-live off"></span> Sin caja abierta</span>
                    @endcan
                @endif
            </div>
        </div>
    </div>

    @include('ventas.partials.alertas')
    @unless ($sesion)
        <div class="alert alert-warning text-white py-2">
            No tienes una caja abierta: podrás guardar pedidos o ventas a crédito sin anticipo.
        </div>
    @endunless

    <form method="POST" action="{{ route('ventas.store') }}" id="form_venta" autocomplete="off">
        @csrf
        @if ($cotizacion)
            <input type="hidden" name="cotizacion_id" value="{{ $cotizacion->id }}">
            <input type="hidden" name="descuento" value="{{ $descuento }}">
        @endif
        <div id="items_hidden"></div>

        <div class="row">
            {{-- ====================== IZQUIERDA: PRODUCTOS ====================== --}}
            <div class="col-xl-5 mb-3">
                <div class="vt-panel">
                    <div class="vt-panel-b">
                        <h6 class="vt-title"><span class="ico"><i class="fas fa-boxes"></i></span> Productos</h6>
                        <div class="vt-search">
                            <i class="fas fa-search lupa"></i>
                            <input type="text" id="buscar" autofocus
                                placeholder="Nombre, código, código de barras o nombre popular…">
                            <button type="button" class="limpiar" id="limpiar" title="Limpiar"><i
                                    class="fas fa-times"></i></button>
                        </div>
                        <div class="vt-meta">
                            <span id="n_result">—</span>
                            <span><i class="fas fa-barcode me-1"></i>Escanea y presiona Enter</span>
                        </div>
                        <div class="vt-grid">
                            <div class="row g-2" id="grid"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ====================== DERECHA: VENTA ====================== --}}
            <div class="col-xl-7 mb-3">
                <div class="vt-panel">
                    <div class="vt-panel-b">
                        <div class="vt-config">
                            <div class="row">
                                @include('ventas.partials.campo_select', [
                                    'name' => 'almacen_id',
                                    'label' => 'Vende desde',
                                    'col' => 4,
                                    'required' => true,
                                    'sinVacio' => true,
                                    'value' => $almacenId,
                                    'options' => $opcionesAlmacen,
                                ])
                                @include('ventas.partials.campo_select', [
                                    'name' => 'cliente_id',
                                    'label' => 'Cliente',
                                    'col' => 5,
                                    'value' => $clienteId,
                                    'options' => $clientesJs->pluck('nombre', 'id'),
                                    'placeholder' => '-- Consumidor final --',
                                ])
                                <div class="col-md-3 mb-3">
                                    <label class="form-label" for="lista_precio">Lista de precios</label>
                                    <select id="lista_precio" name="lista_precio" class="form-select"
                                        @unless ($privilegiado) disabled @endunless>
                                        <option value="menor">Menor</option>
                                        <option value="mayor">Mayorista</option>
                                        <option value="distribuidor">Distribuidor</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="vt-cart-head mb-2">
                            <h6 class="vt-title mb-0"><span class="ico"><i class="fas fa-shopping-basket"></i></span>
                                Detalle de la venta</h6>
                            <span class="vt-count" id="n_items">0 productos</span>
                        </div>

                        <div id="carrito"></div>
                        <div id="vacio" class="vt-empty">
                            <i class="fas fa-cart-plus"></i>
                            Aún no agregaste productos.<br>
                            <small>Toca un producto de la izquierda o escanea su código.</small>
                        </div>
                        <small class="text-muted d-block mb-3">Toca el descuento de cada línea para cambiarlo
                            ({{ $privilegiado ? '0, 5, 10 %' : '0, 5 %' }}).@unless ($privilegiado)
                                Tu rol puede dar hasta 5 %.
                            @endunless
                        </small>

                        <div class="vt-totales mb-3">
                            <div class="fila"><span>Subtotal</span><span id="t_sub">Bs 0,00</span></div>
                            <div class="fila"><span>Descuento</span><span id="t_desc">− Bs 0,00</span></div>
                            <div class="total"><span>Total</span><span id="t_total">Bs 0,00</span></div>
                        </div>

                        <div class="vt-pago">
                            <label class="form-label">Tipo de pago</label>
                            <input type="hidden" id="tipo_pago" name="tipo_pago" value="{{ old('tipo_pago', 'contado') }}">
                            <div class="vt-seg mb-3" id="seg_pago">
                                <button type="button" data-tipo="contado"><i class="fas fa-money-bill-wave me-1"></i>
                                    Contado</button>
                                <button type="button" data-tipo="credito"><i class="fas fa-hand-holding-usd me-1"></i>
                                    Crédito</button>
                            </div>
                            <div class="row">
                                @include('ventas.partials.campo_select', [
                                    'name' => 'metodo_pago_id',
                                    'label' => 'Método de pago',
                                    'col' => 6,
                                    'value' => $efectivoId,
                                    'options' => $metodos,
                                    'placeholder' => '-- Seleccione --',
                                ])
                                <div class="col-md-6 mb-3" id="bloque_anticipo" style="display:none;">
                                    <label class="form-label" for="anticipo">Anticipo (Bs)</label>
                                    <input type="number" step="0.01" min="0" id="anticipo" name="anticipo"
                                        value="{{ old('anticipo', 0) }}" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                @include('ventas.partials.campo_texto', [
                                    'name' => 'observaciones',
                                    'label' => 'Observaciones',
                                    'col' => 7,
                                    'maxlength' => 1000,
                                ])
                                @include('ventas.partials.campo_check', [
                                    'name' => 'como_pedido',
                                    'label' => 'Guardar como pedido (no descuenta stock ni cobra)',
                                    'col' => 5,
                                    'value' => false,
                                ])
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('ventas.index') }}" class="vt-cancel text-center">Cancelar</a>
                            <button type="submit" class="vt-cta flex-grow-1" id="btn_cobrar"><i class="fas fa-check"></i>
                                Cobrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const CAT = @json($catalogo);
            const CLI = @json($clientesJs);
            const FIJOS = @json($items);
            const PRIV = @json($privilegiado);
            const DESC_COT = {{ (float) $descuento }};
            const OPC_DESC = PRIV ? [0, 5, 10] : [0, 5];
            const COLORES = ['#F7D56E', '#D9C7A0', '#E8B44F', '#F0C987', '#A8D5A2', '#C9B6E4', '#92C3E4'];
            const $ = id => document.getElementById(id);
            const fmt = n => Number(n || 0).toLocaleString('es-BO', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [c]));
            const porId = {};
            CAT.forEach(p => porId[p.id] = p);
            let cart = []; // { pid, pi (índice de presentación), qty, pct, fijo: {precio, desc} | null }

            const lista = () => $('lista_precio').value || 'menor';
            const disponible = p => Number((p.stock || {})[$('almacen_id').value] || 0);
            const precioDe = (pres) => pres[lista()] > 0 ? pres[lista()] : pres.menor;
            const thumb = (p, clase) => p.imagen ?
                `<img src="${esc(p.imagen)}" alt="" class="${clase === 'g' ? 'vt-img' : 'vt-th'}">` :
                `<span class="${clase === 'g' ? 'vt-ini' : 'vt-th'}" style="background:linear-gradient(135deg,${COLORES[p.id % COLORES.length]},${COLORES[(p.id + 3) % COLORES.length]})">${esc(p.nombre.substring(0, 3).toUpperCase())}</span>`;
            const coincide = (p, q) => !q || [p.nombre, p.codigo, p.barras, p.popular].join(' ').toLowerCase()
                .includes(q);

            // ---------- Catálogo ----------
            function pintarGrid() {
                const q = $('buscar').value.trim().toLowerCase();
                const items = CAT.filter(p => coincide(p, q));
                $('limpiar').style.display = q ? 'block' : 'none';
                $('n_result').textContent = items.length + (items.length === 1 ? ' producto' : ' productos');
                $('grid').innerHTML = items.length ? items.map(p => {
                        const disp = disponible(p),
                            base = p.presentaciones[0];
                        const cls = disp <= 0 ? 'none' : (disp <= 5 ? 'low' : '');
                        return `<div class="col-6 col-md-4"><div class="vt-card ${disp <= 0 ? 'agotado' : ''}" data-add="${p.id}">
                <span class="vt-stock ${cls}">${disp > 0 ? disp + ' unid.' : 'Sin stock'}</span>
                ${thumb(p, 'g')}
                <div class="vt-pname" title="${esc(p.nombre)}">${esc(p.nombre)}</div>
                <div class="vt-pcode">${esc(p.codigo)}${p.presentaciones.length > 1 ? ' · <span class="vt-npres">' + p.presentaciones.length + ' presentaciones</span>' : ''}</div>
                <div class="vt-price"><b>Bs ${fmt(precioDe(base))}</b><span class="mas"><i class="fas fa-plus"></i></span></div>
            </div></div>`;
                    }).join('') :
                    '<div class="col-12 vt-empty"><i class="fas fa-search"></i>No se encontraron productos.</div>';
            }

            // ---------- Carrito ----------
            function agregar(pid, qty) {
                const p = porId[pid];
                if (!p) return;
                const linea = cart.find(l => l.pid === pid && !l.fijo);
                if (linea) {
                    linea.qty += (qty || 1);
                } else {
                    cart.push({
                        pid,
                        pi: 0,
                        qty: qty || 1,
                        pct: 0,
                        fijo: null
                    });
                }
                pintarCarrito();
            }

            function pintarCarrito() {
                let sub = 0,
                    desc = 0,
                    html = '',
                    hid = '',
                    i = 0;
                cart.forEach((l, idx) => {
                    const p = porId[l.pid],
                        pres = p.presentaciones[l.pi];
                    const precio = l.fijo ? l.fijo.precio : precioDe(pres);
                    const bruto = l.qty * precio;
                    const d = l.fijo ? l.fijo.desc : bruto * l.pct / 100;
                    sub += bruto;
                    desc += d;
                    const base = l.qty * (l.fijo ? 1 : pres.unidades);
                    const exceso = base > disponible(p) + 0.0005;
                    html += `<div class="vt-line ${exceso ? 'exceso' : ''}">
                <div class="vt-l1">
                    ${thumb(p, 'c')}
                    <div class="nom"><b>${esc(p.nombre)}</b><small>${esc(p.codigo)}</small></div>
                    <div class="sub">Bs ${fmt(bruto - d)}</div>
                    <button type="button" class="vt-x" data-quitar="${idx}" title="Quitar"><i class="fas fa-times"></i></button>
                </div>
                <div class="vt-l2">
                    ${l.fijo ? '<div class="vt-pres fijo"><span>Unidad</span></div>' : `<div class="vt-pres">${p.presentaciones.map((x, k) => `<button type="button" class="${k === l.pi ? 'on' : ''}" data-pres="${idx}:${k}">${esc(x.nombre)}</button>`).join('')}</div>`}
                    <div class="vt-step">
                        <button type="button" data-menos="${idx}">−</button>
                        <input type="number" step="any" min="0.001" value="${l.qty}" data-qty="${idx}">
                        <button type="button" data-mas="${idx}">+</button>
                    </div>
                    <span class="vt-eq ${exceso ? 'bad' : ''}">${l.fijo ? '' : '= ' + base + ' unid.'}${exceso ? ' · solo hay ' + disponible(p) : ''}</span>
                    <div class="vt-unit">
                        <span>Bs ${fmt(precio)} c/u</span>
                        ${l.fijo ? (d > 0 ? `<span class="vt-desc on">− Bs ${fmt(d)}</span>` : '') : `<button type="button" class="vt-desc ${l.pct > 0 ? 'on' : ''}" data-desc="${idx}" title="Cambiar descuento">${l.pct > 0 ? '−' + l.pct + '%' : 'Desc.'}</button>`}
                    </div>
                </div>
            </div>`;
                    hid += `<input type="hidden" name="items[${i}][producto_id]" value="${p.id}">
                    <input type="hidden" name="items[${i}][cantidad]" value="${l.qty}">`;
                    if (l.fijo) {
                        hid +=
                            `<input type="hidden" name="items[${i}][precio_fijo]" value="1"><input type="hidden" name="items[${i}][precio_unitario]" value="${l.fijo.precio}"><input type="hidden" name="items[${i}][descuento]" value="${l.fijo.desc}">`;
                    } else {
                        if (pres.id) hid +=
                            `<input type="hidden" name="items[${i}][presentacion_id]" value="${pres.id}">`;
                        hid += `<input type="hidden" name="items[${i}][descuento_pct]" value="${l.pct}">`;
                    }
                    i++;
                });
                $('carrito').innerHTML = html;
                $('items_hidden').innerHTML = hid;
                $('vacio').style.display = cart.length ? 'none' : '';
                $('n_items').textContent = cart.length + (cart.length === 1 ? ' producto' : ' productos');
                const total = sub - desc - DESC_COT;
                $('t_sub').textContent = 'Bs ' + fmt(sub);
                $('t_desc').textContent = '− Bs ' + fmt(desc + DESC_COT);
                $('t_total').textContent = 'Bs ' + fmt(total);
                actualizarBoton(total);
            }

            function actualizarBoton(total) {
                const pedido = $('como_pedido') && $('como_pedido').checked;
                const credito = $('tipo_pago').value === 'credito';
                const btn = $('btn_cobrar');
                btn.className = 'vt-cta flex-grow-1' + (pedido ? ' pedido' : (credito ? ' credito' : ''));
                btn.innerHTML = '<i class="fas fa-' + (pedido ? 'clipboard-list' : 'check') + '"></i> ' + (pedido ?
                    'Guardar pedido' : (credito ? 'Registrar venta a crédito' : 'Cobrar')) + (total > 0 ?
                    ' · Bs ' + fmt(total) : '');
                $('bloque_anticipo').style.display = credito ? '' : 'none';
                document.querySelectorAll('#seg_pago button').forEach(b => b.classList.toggle('on', b.dataset
                    .tipo === $('tipo_pago').value));
            }

            // ---------- Eventos ----------
            $('grid').addEventListener('click', e => {
                const c = e.target.closest('[data-add]');
                if (!c) return;
                agregar(Number(c.dataset.add));
                c.classList.remove('flash');
                void c.offsetWidth;
                c.classList.add('flash');
            });
            $('carrito').addEventListener('click', e => {
                const b = e.target.closest('button');
                if (!b) return;
                const d = b.dataset;
                if (d.pres !== undefined) {
                    const [i, k] = d.pres.split(':').map(Number);
                    cart[i].pi = k;
                } else if (d.menos !== undefined) {
                    cart[d.menos].qty = Math.max(1, Math.round((cart[d.menos].qty - 1) * 1000) / 1000);
                } else if (d.mas !== undefined) {
                    cart[d.mas].qty = Math.round((cart[d.mas].qty + 1) * 1000) / 1000;
                } else if (d.desc !== undefined) {
                    const l = cart[d.desc];
                    l.pct = OPC_DESC[(OPC_DESC.indexOf(l.pct) + 1) % OPC_DESC.length];
                } else if (d.quitar !== undefined) {
                    cart.splice(d.quitar, 1);
                } else return;
                pintarCarrito();
            });
            $('carrito').addEventListener('change', e => {
                if (e.target.dataset.qty === undefined) return;
                const v = parseFloat(e.target.value);
                cart[e.target.dataset.qty].qty = v > 0 ? v : 1;
                pintarCarrito();
            });
            $('buscar').addEventListener('input', pintarGrid);
            $('limpiar').addEventListener('click', () => {
                $('buscar').value = '';
                pintarGrid();
                $('buscar').focus();
            });
            $('buscar').addEventListener('keydown', e => {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                const q = $('buscar').value.trim().toLowerCase();
                if (!q) return;
                const exacto = CAT.find(p => (p.barras || '').toLowerCase() === q || (p.codigo || '')
                    .toLowerCase() === q);
                const visibles = CAT.filter(p => coincide(p, q));
                const elegido = exacto || (visibles.length === 1 ? visibles[0] : null);
                if (elegido) {
                    agregar(elegido.id);
                    $('buscar').value = '';
                    pintarGrid();
                }
            });
            $('almacen_id').addEventListener('change', () => {
                pintarGrid();
                pintarCarrito();
            });
            $('lista_precio').addEventListener('change', () => {
                pintarGrid();
                pintarCarrito();
            });
            $('cliente_id').addEventListener('change', () => {
                const c = CLI.find(x => String(x.id) === $('cliente_id').value);
                $('lista_precio').value = c ? c.lista : 'menor';
                pintarGrid();
                pintarCarrito();
            });
            $('seg_pago').addEventListener('click', e => {
                const b = e.target.closest('button');
                if (!b) return;
                $('tipo_pago').value = b.dataset.tipo;
                pintarCarrito();
            });
            if ($('como_pedido')) $('como_pedido').addEventListener('change', () => pintarCarrito());

            $('form_venta').addEventListener('submit', e => {
                if (!cart.length) {
                    e.preventDefault();
                    alert('Agrega al menos un producto.');
                    return;
                }
                const pedido = $('como_pedido') && $('como_pedido').checked;
                const sinStock = cart.find(l => {
                    const p = porId[l.pid];
                    return l.qty * (l.fijo ? 1 : p.presentaciones[l.pi].unidades) > disponible(p) +
                        0.0005;
                });
                if (!pedido && sinStock) {
                    e.preventDefault();
                    alert('«' + porId[sinStock.pid].nombre +
                        '» no tiene stock suficiente en este almacén.');
                    return;
                }
                $('btn_cobrar').disabled = true;
            });

            // Estado inicial: cliente preseleccionado (cotización) y líneas de la cotización
            const c0 = CLI.find(x => String(x.id) === $('cliente_id').value);
            if (c0) $('lista_precio').value = c0.lista;
            FIJOS.forEach(f => {
                if (porId[f.producto_id]) cart.push({
                    pid: f.producto_id,
                    pi: 0,
                    qty: f.cantidad,
                    pct: 0,
                    fijo: {
                        precio: f.precio_unitario,
                        desc: f.descuento
                    }
                });
            });
            pintarGrid();
            pintarCarrito();
        });
    </script>

@endsection