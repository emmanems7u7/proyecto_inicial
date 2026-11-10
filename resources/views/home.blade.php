@extends('layouts.argon')

@section('content')
    <style>
        .minimal-card-horizontal {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .minimal-card-horizontal:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .response-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: #fff;
            font-weight: bold;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .form-name {
            font-size: 0.95rem;
            font-weight: 500;
            color: #212529;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
    @if ($tiempo_cambio_contraseña != 1)
        <div class="container mb-3">





            @include('ventas.partials.panel_estilos')

            @php
                $hora = now()->hour;
                $saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
                $nombre = optional(auth()->user())->nombre_completo ?? (optional(auth()->user())->name ?? '');
                $i = 0; // contador para la entrada escalonada
            @endphp

            <div class="pd">

                @include('ventas.partials.alertas')

                {{-- ====================== HERO + ACCIONES ====================== --}}
                <div class="pd-hero pd-in" style="--i: {{ $i++ }}">
                    <span class="pd-orb a"></span>
                    <span class="pd-orb b"></span>

                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <h1>{{ $saludo }}@if ($nombre)
                                    , {{ \Illuminate\Support\Str::before($nombre, ' ') }}
                                @endif <span class="pd-wave">👋</span></h1>
                            <div class="pd-sub">
                                Panel de control
                                @if ($tienda)
                                    · <i class="fas fa-store me-1"></i>{{ $tienda->nombre }}
                                @endif
                                · {{ now()->translatedFormat('l d \d\e F') }}
                            </div>
                        </div>

                        <div>
                            @if (array_key_exists('mi_sesion', $d))
                                @if ($d['mi_sesion'])
                                    <a class="pd-pill" href="{{ route('caja.sesiones.show', $d['mi_sesion']) }}">
                                        <span class="pd-live"></span>
                                        Caja <strong>{{ $d['mi_sesion']->caja->nombre }}</strong> abierta desde las
                                        {{ $d['mi_sesion']->fecha_apertura->format('H:i') }}
                                    </a>
                                @else
                                    <span class="pd-pill"><span class="pd-live off"></span> No tienes una caja
                                        abierta</span>
                                @endif
                            @else
                                <span class="pd-pill"><i class="fas fa-info-circle"></i> Los bloques dependen de tus
                                    permisos</span>
                            @endif
                        </div>
                    </div>

                    <div class="pd-actions">
                        @can('ventas.crear')
                            <a href="{{ route('ventas.create') }}" class="pd-action" style="--c: #10b981">
                                <span class="ico"><i class="fas fa-cash-register"></i></span> Nueva venta
                            </a>
                        @endcan
                        @can('caja.abrir')
                            @if (empty($d['mi_sesion']))
                                <a href="{{ route('caja.abrir.create') }}" class="pd-action" style="--c: #0ea5e9">
                                    <span class="ico"><i class="fas fa-lock-open"></i></span> Abrir caja
                                </a>
                            @endif
                        @endcan
                        @can('compras.crear')
                            <a href="{{ route('compras.create') }}" class="pd-action" style="--c: #8b5cf6">
                                <span class="ico"><i class="fas fa-truck-loading"></i></span> Nueva compra
                            </a>
                        @endcan
                        @can('inventario.movimientos')
                            <a href="{{ route('inventario.movimiento.create') }}" class="pd-action" style="--c: #f59e0b">
                                <span class="ico"><i class="fas fa-exchange-alt"></i></span> Movimiento de stock
                            </a>
                        @endcan
                        @can('transferencias.crear')
                            <a href="{{ route('transferencias.create') }}" class="pd-action" style="--c: #f43f5e">
                                <span class="ico"><i class="fas fa-dolly"></i></span> Transferencia
                            </a>
                        @endcan
                    </div>
                </div>

                {{-- ====================== KPIs ====================== --}}
                @php
                    $kpis = [];
                    if (isset($d['ventas_hoy'])) {
                        $kpis[] = [
                            'lbl' => 'Ventas de hoy',
                            'ico' => 'fa-chart-line',
                            'c' => '#10b981',
                            'c2' => '#059669',
                            'raw' => (float) $d['ventas_hoy'],
                            'dec' => 2,
                            'txt' => 'Bs ' . \App\Support\Formato::dinero($d['ventas_hoy']),
                            'foot' => null,
                            'bad' => false,
                        ];
                        $kpis[] = [
                            'lbl' => 'N.º de ventas hoy',
                            'ico' => 'fa-receipt',
                            'c' => '#4f46e5',
                            'c2' => '#7c3aed',
                            'raw' => (float) $d['ventas_hoy_nro'],
                            'dec' => 0,
                            'txt' => $d['ventas_hoy_nro'],
                            'foot' => null,
                            'bad' => false,
                        ];
                        $kpis[] = [
                            'lbl' => 'Ventas del mes',
                            'ico' => 'fa-calendar-alt',
                            'c' => '#0ea5e9',
                            'c2' => '#06b6d4',
                            'raw' => (float) $d['ventas_mes'],
                            'dec' => 2,
                            'txt' => 'Bs ' . \App\Support\Formato::dinero($d['ventas_mes']),
                            'foot' => null,
                            'bad' => false,
                        ];
                    }
                    if (isset($d['cxc_saldo'])) {
                        $kpis[] = [
                            'lbl' => 'Por cobrar',
                            'ico' => 'fa-hand-holding-usd',
                            'c' => '#f59e0b',
                            'c2' => '#f97316',
                            'raw' => (float) $d['cxc_saldo'],
                            'dec' => 2,
                            'txt' => 'Bs ' . \App\Support\Formato::dinero($d['cxc_saldo']),
                            'foot' =>
                                'Vencido: Bs ' .
                                \App\Support\Formato::dinero($d['cxc_vencido']) .
                                ' (' .
                                $d['cxc_vencidas_nro'] .
                                ')',
                            'bad' => (float) $d['cxc_vencido'] > 0,
                        ];
                    }
                    if (isset($d['cxp_saldo'])) {
                        $kpis[] = [
                            'lbl' => 'Por pagar',
                            'ico' => 'fa-file-invoice-dollar',
                            'c' => '#f43f5e',
                            'c2' => '#e11d48',
                            'raw' => (float) $d['cxp_saldo'],
                            'dec' => 2,
                            'txt' => 'Bs ' . \App\Support\Formato::dinero($d['cxp_saldo']),
                            'foot' =>
                                'Vencido: Bs ' .
                                \App\Support\Formato::dinero($d['cxp_vencido']) .
                                ' (' .
                                $d['cxp_vencidas_nro'] .
                                ')',
                            'bad' => (float) $d['cxp_vencido'] > 0,
                        ];
                    }
                @endphp

                @if (count($kpis))
                    <div class="row g-3 mt-1">
                        @foreach ($kpis as $k)
                            <div class="col-12 col-sm-6 col-xl pd-in" style="--i: {{ $i++ }}">
                                <div class="pd-card pd-kpi" style="--c: {{ $k['c'] }}; --c2: {{ $k['c2'] }}">
                                    <span class="pd-chip"><i class="fas {{ $k['ico'] }}"></i></span>
                                    <div class="lbl">{{ $k['lbl'] }}</div>
                                    <div class="val" data-count="{{ $k['raw'] }}" data-dec="{{ $k['dec'] }}">
                                        {{ $k['txt'] }}</div>
                                    @if ($k['foot'])
                                        <div class="foot {{ $k['bad'] ? 'bad' : '' }}">
                                            @if ($k['bad'])
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                            @endif{{ $k['foot'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ====================== VENTAS 7 DÍAS + TOP PRODUCTOS ====================== --}}
                @if (isset($d['serie']))
                    @php
                        $serieMax = max(1, (float) ($d['serie_max'] ?? 0));
                        $totalSerie = collect($d['serie'])->count();
                        $topMax = max(1, (float) collect($d['top_productos'])->max('total'));
                    @endphp
                    <div class="row g-3 mt-1">
                        <div class="col-lg-7 pd-in" style="--i: {{ $i++ }}">
                            <div class="pd-card">
                                <div class="pd-card-b">
                                    <h6 class="pd-title" style="--c: #4f46e5"><span class="t-ico"><i
                                                class="fas fa-chart-bar"></i></span> Ventas de los últimos 7 días</h6>
                                    <div class="pd-bars">
                                        @foreach ($d['serie'] as $n => $p)
                                            @php $alto = max(2, round(($p['total'] / $serieMax) * 100)); @endphp
                                            <div class="pd-col {{ $loop->last ? 'hoy' : '' }}"
                                                title="{{ $p['dia'] }}: Bs {{ \App\Support\Formato::dinero($p['total']) }}">
                                                <span class="amt">{{ \App\Support\Formato::dinero($p['total']) }}</span>
                                                <div class="bar"
                                                    style="height: {{ $alto }}%; --k: {{ $n }}"></div>
                                                <span class="dia">{{ $p['dia'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5 pd-in" style="--i: {{ $i++ }}">
                            <div class="pd-card">
                                <div class="pd-card-b">
                                    <h6 class="pd-title" style="--c: #f59e0b"><span class="t-ico"><i
                                                class="fas fa-trophy"></i></span> Más vendidos del mes</h6>
                                    @forelse ($d['top_productos'] as $tp)
                                        <div class="pd-row">
                                            <span class="pd-rank">{{ $loop->iteration }}</span>
                                            <div class="main">
                                                <div class="name">{{ $tp->nombre }}</div>
                                                <div class="pd-meter"><span
                                                        style="width: {{ round(($tp->total / $topMax) * 100) }}%; --c: #f59e0b"></span>
                                                </div>
                                            </div>
                                            <div class="side">Bs {{ \App\Support\Formato::dinero($tp->total) }}
                                                <small>{{ \App\Support\Formato::cantidad($tp->cant) }} uds.</small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="pd-empty neutral"><i class="fas fa-box-open"></i>Aún no hay ventas este
                                            mes.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ====================== STOCK BAJO + LOTES ====================== --}}
                @if (isset($d['bajo_minimo_nro']))
                    <div class="row g-3 mt-1">
                        <div class="col-lg-6 pd-in" style="--i: {{ $i++ }}">
                            <div class="pd-card">
                                <div class="pd-card-b">
                                    <h6 class="pd-title" style="--c: #f43f5e">
                                        <span class="t-ico"><i class="fas fa-boxes"></i></span> Stock bajo el mínimo
                                        <span
                                            class="pd-tag badge-n {{ $d['bajo_minimo_nro'] ? 'bad pulse' : 'ok' }}">{{ $d['bajo_minimo_nro'] }}</span>
                                    </h6>
                                    @forelse ($d['bajo_minimo'] as $it)
                                        @php
                                            $pct =
                                                $it->stock_minimo > 0
                                                    ? min(100, round(($it->cantidad / $it->stock_minimo) * 100))
                                                    : 0;
                                            $col = $pct <= 30 ? '#f43f5e' : '#f59e0b';
                                        @endphp
                                        <div class="pd-row">
                                            <div class="main">
                                                <div class="name">{{ $it->producto->nombre }}</div>
                                                <div class="meta"><i
                                                        class="fas fa-warehouse me-1"></i>{{ $it->almacen->nombre }}</div>
                                                <div class="pd-meter"><span
                                                        style="width: {{ max(3, $pct) }}%; --c: {{ $col }}"></span>
                                                </div>
                                            </div>
                                            <div class="side" style="color: {{ $col }}">
                                                {{ \App\Support\Formato::cantidad($it->cantidad) }}
                                                <small>mín. {{ \App\Support\Formato::cantidad($it->stock_minimo) }}</small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="pd-empty"><i class="fas fa-check-circle"></i>Todo el stock está sobre
                                            el mínimo.</div>
                                    @endforelse
                                    @can('reportes.inventario')
                                        <div class="mt-3"><a class="pd-link"
                                                href="{{ route('reportes.inventario', ['tipo' => 'bajo_minimo']) }}">Ver
                                                reporte completo <i class="fas fa-arrow-right ms-1"></i></a></div>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 pd-in" style="--i: {{ $i++ }}">
                            <div class="pd-card">
                                <div class="pd-card-b">
                                    <h6 class="pd-title" style="--c: #f59e0b">
                                        <span class="t-ico"><i class="fas fa-hourglass-half"></i></span> Lotes por vencer
                                        <span class="badge-n d-flex gap-1">
                                            <span
                                                class="pd-tag {{ $d['lotes_nro'] ? 'warn' : 'ok' }}">{{ $d['lotes_nro'] }}</span>
                                            @if ($d['lotes_vencidos'])
                                                <span class="pd-tag bad pulse">{{ $d['lotes_vencidos'] }} vencidos</span>
                                            @endif
                                        </span>
                                    </h6>
                                    @forelse ($d['lotes'] as $l)
                                        @php
                                            $dias = $l->dias_para_vencer;
                                            $cls = $dias < 0 ? 'bad' : ($dias <= 15 ? 'warn' : 'info');
                                        @endphp
                                        <div class="pd-row">
                                            <div class="main">
                                                <div class="name">{{ $l->producto->nombre }}</div>
                                                <div class="meta">Lote {{ $l->numero_lote }} · <i
                                                        class="fas fa-warehouse me-1"></i>{{ $l->almacen->nombre }}</div>
                                            </div>
                                            <div class="side">
                                                {{ $l->fecha_vencimiento->format('d/m/Y') }}
                                                <small><span
                                                        class="pd-tag {{ $cls }}">{{ $dias < 0 ? 'Venció hace ' . abs($dias) . ' d' : $dias . ' d' }}</span></small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="pd-empty"><i class="fas fa-check-circle"></i>Sin lotes próximos a
                                            vencer.</div>
                                    @endforelse
                                    @can('reportes.inventario')
                                        <div class="mt-3"><a class="pd-link"
                                                href="{{ route('reportes.inventario', ['tipo' => 'vencimientos']) }}">Ver
                                                reporte completo <i class="fas fa-arrow-right ms-1"></i></a></div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ====================== CAJAS + TRANSFERENCIAS ====================== --}}
                @php
                    $hayCajas = isset($d['cajas_abiertas']) && $d['cajas_abiertas']->isNotEmpty();
                    $hayTransf =
                        isset($d['transferencias_en_transito']) && $d['transferencias_en_transito']->isNotEmpty();
                @endphp
                @if ($hayCajas || $hayTransf)
                    <div class="row g-3 mt-1 mb-3">
                        @if ($hayCajas)
                            <div class="col-lg-6 pd-in" style="--i: {{ $i++ }}">
                                <div class="pd-card">
                                    <div class="pd-card-b">
                                        <h6 class="pd-title" style="--c: #10b981">
                                            <span class="t-ico"><i class="fas fa-cash-register"></i></span> Cajas
                                            abiertas ahora
                                            <span class="pd-live badge-n ms-auto"></span>
                                        </h6>
                                        @foreach ($d['cajas_abiertas'] as $s)
                                            <div class="pd-row">
                                                <span class="pd-rank" style="background:#d1fae5;color:#047857"><i
                                                        class="fas fa-user"></i></span>
                                                <div class="main">
                                                    <div class="name">{{ $s->caja->nombre }}</div>
                                                    <div class="meta">{{ $s->usuarioApertura->nombre_completo ?? '' }}
                                                    </div>
                                                </div>
                                                <a class="pd-go" href="{{ route('caja.sesiones.show', $s) }}"
                                                    title="Ver caja"><i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($hayTransf)
                            <div class="col-lg-6 pd-in" style="--i: {{ $i++ }}">
                                <div class="pd-card">
                                    <div class="pd-card-b">
                                        <h6 class="pd-title" style="--c: #0ea5e9"><span class="t-ico"><i
                                                    class="fas fa-truck"></i></span> Transferencias por recibir</h6>
                                        @foreach ($d['transferencias_en_transito'] as $t)
                                            <div class="pd-row">
                                                <span class="pd-rank" style="background:#e0f2fe;color:#0369a1"><i
                                                        class="fas fa-dolly"></i></span>
                                                <div class="main">
                                                    <div class="name">{{ $t->numero }}</div>
                                                    <div class="meta"><i
                                                            class="fas fa-long-arrow-alt-right me-1"></i>{{ $t->almacenDestino->nombre }}
                                                    </div>
                                                </div>
                                                <a class="pd-go" href="{{ route('transferencias.show', $t) }}"
                                                    title="Ver transferencia"><i class="fas fa-arrow-right"></i></a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            </div>

            {{-- Contador animado en las tarjetas (el valor final vuelve al formato del servidor) --}}
            <script>
                (function() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    document.querySelectorAll('.pd [data-count]').forEach(function(el) {
                        var end = parseFloat(el.dataset.count) || 0;
                        if (!end) return;
                        var dec = parseInt(el.dataset.dec || 0, 10);
                        var prefix = el.textContent.indexOf('Bs') === 0 ? 'Bs ' : '';
                        var final = el.textContent,
                            t0 = null,
                            dur = 1200;

                        function paso(ts) {
                            if (!t0) t0 = ts;
                            var p = Math.min((ts - t0) / dur, 1),
                                e = 1 - Math.pow(1 - p, 3);
                            el.textContent = prefix + (end * e).toLocaleString('es-BO', {
                                minimumFractionDigits: dec,
                                maximumFractionDigits: dec
                            });
                            if (p < 1) requestAnimationFrame(paso);
                            else el.textContent = final;
                        }
                        requestAnimationFrame(paso);
                    });
                })();
            </script>




        </div>
    @else
        <div class="alert alert-warning" role="alert">
            <strong>!Alerta!</strong> Debes actualizar tu contraseña
        </div>
    @endif
@endsection
