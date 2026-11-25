@extends('layouts.argon')

@section('content')
    <style>
        /* Tarjeta de tienda: usa las variables --c / --c2 igual que las tarjetas del panel */
        .pt-store {
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .pt-store:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .14);
        }

        .pt-store::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 5px;
            background: linear-gradient(90deg, var(--c), var(--c2));
        }

        .pt-store.actual {
            box-shadow: 0 0 0 2px var(--c), 0 10px 26px rgba(15, 23, 42, .12);
        }

        .pt-head {
            display: flex;
            align-items: center;
            gap: .85rem;
            margin-bottom: 1rem;
        }

        .pt-badge {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: .8rem;
            letter-spacing: .02em;
            background: linear-gradient(135deg, var(--c), var(--c2));
            box-shadow: 0 6px 14px color-mix(in srgb, var(--c) 45%, transparent);
        }

        .pt-name {
            font-weight: 700;
            font-size: 1rem;
            color: #1f2937;
            line-height: 1.2;
        }

        .pt-city {
            font-size: .78rem;
            color: #6b7280;
        }

        .pt-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .5rem 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: .85rem;
            color: #4b5563;
        }

        .pt-stat:last-of-type {
            border-bottom: 0;
        }

        .pt-stat strong {
            color: #111827;
        }

        .pt-sales {
            font-size: 1.45rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
        }

        .pt-sales small {
            font-size: .75rem;
            font-weight: 600;
            color: #6b7280;
            margin-right: .2rem;
        }

        .pt-enter {
            display: block;
            width: 100%;
            margin-top: auto;
            border: 0;
            border-radius: 12px;
            padding: .7rem 1rem;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, var(--c), var(--c2));
            transition: filter .15s, transform .15s;
        }

        .pt-enter:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .pt-enter i {
            transition: transform .15s;
        }

        .pt-enter:hover i {
            transform: translateX(4px);
        }

        .pt-here {
            position: absolute;
            top: 14px;
            right: 14px;
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
                $paleta = [
                    ['#4f46e5', '#7c3aed'],
                    ['#10b981', '#059669'],
                    ['#0ea5e9', '#06b6d4'],
                    ['#f59e0b', '#f97316'],
                    ['#f43f5e', '#e11d48'],
                    ['#8b5cf6', '#a855f7'],
                ];
                $maxVentas = max(1, (float) $tarjetas->max('ventas_hoy'));
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
                                Mis tiendas · Elige la sucursal con la que vas a trabajar
                                · {{ now()->translatedFormat('l d \d\e F') }}
                            </div>
                        </div>

                        <div>
                            @if (isset($tiendaActual) && $tiendaActual)
                                <span class="pd-pill"><span class="pd-live"></span> Tienda activa:
                                    <strong>{{ $tiendaActual->nombre }}</strong></span>
                            @else
                                <span class="pd-pill"><span class="pd-live off"></span> Aún no eliges una tienda</span>
                            @endif
                        </div>
                    </div>

                    @if ($puedeCrear || $puedeReportes)
                        <div class="pd-actions">
                            @if ($puedeCrear)
                                <a href="{{ route('tiendas.create') }}" class="pd-action" style="--c: #10b981">
                                    <span class="ico"><i class="fas fa-plus"></i></span> Nueva tienda
                                </a>
                            @endif
                            @if ($puedeReportes)
                                <a href="{{ route('reportes.globales') }}" class="pd-action" style="--c: #0ea5e9">
                                    <span class="ico"><i class="fas fa-chart-pie"></i></span> Reportes globales
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ====================== KPIs ====================== --}}
                @php
                    $kpis = [
                        [
                            'lbl' => 'Ventas de hoy (todas)',
                            'ico' => 'fa-chart-line',
                            'c' => '#10b981',
                            'c2' => '#059669',
                            'raw' => (float) $resumen['ventas_hoy'],
                            'dec' => 2,
                            'txt' => 'Bs ' . \App\Support\Formato::dinero($resumen['ventas_hoy']),
                        ],
                        [
                            'lbl' => 'Tiendas disponibles',
                            'ico' => 'fa-store',
                            'c' => '#4f46e5',
                            'c2' => '#7c3aed',
                            'raw' => (float) $tarjetas->count(),
                            'dec' => 0,
                            'txt' => $tarjetas->count(),
                        ],
                        [
                            'lbl' => 'Usuarios',
                            'ico' => 'fa-users',
                            'c' => '#0ea5e9',
                            'c2' => '#06b6d4',
                            'raw' => (float) $resumen['usuarios'],
                            'dec' => 0,
                            'txt' => $resumen['usuarios'],
                        ],
                        [
                            'lbl' => 'Cuentas por cobrar',
                            'ico' => 'fa-hand-holding-usd',
                            'c' => '#f59e0b',
                            'c2' => '#f97316',
                            'raw' => (float) $resumen['cxc'],
                            'dec' => 2,
                            'txt' => 'Bs ' . \App\Support\Formato::dinero($resumen['cxc']),
                        ],
                    ];
                @endphp

                <div class="row g-3 mt-1">
                    @foreach ($kpis as $k)
                        <div class="col-12 col-sm-6 col-xl-3 pd-in" style="--i: {{ $i++ }}">
                            <div class="pd-card pd-kpi" style="--c: {{ $k['c'] }}; --c2: {{ $k['c2'] }}">
                                <span class="pd-chip"><i class="fas {{ $k['ico'] }}"></i></span>
                                <div class="lbl">{{ $k['lbl'] }}</div>
                                <div class="val" data-count="{{ $k['raw'] }}" data-dec="{{ $k['dec'] }}">
                                    {{ $k['txt'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ====================== TIENDAS ====================== --}}
                <h6 class="pd-title mt-4 mb-2" style="--c: #4f46e5"><span class="t-ico"><i class="fas fa-store"></i></span>
                    Mis tiendas ({{ $tarjetas->count() }})</h6>

                @if ($tarjetas->isEmpty())
                    <div class="pd-card pd-in" style="--i: {{ $i++ }}">
                        <div class="pd-card-b">
                            <div class="pd-empty neutral"><i class="fas fa-store-slash"></i>No tienes tiendas asignadas.
                                Pide al administrador que te asigne una.</div>
                        </div>
                    </div>
                @endif

                <div class="row g-3 mb-3">
                    @foreach ($tarjetas as $n => $c)
                        @php
                            $t = $c['tienda'];
                            [$col1, $col2] = $paleta[$n % count($paleta)];
                            $esActual = isset($tiendaActual) && $tiendaActual && $tiendaActual->id === $t->id;
                            $pct = round(($c['ventas_hoy'] / $maxVentas) * 100);
                            $claseAlerta = $c['alerta']
                                ? (str_contains($c['alerta'], 'vencer')
                                    ? 'warn'
                                    : 'bad')
                                : 'ok';
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4 pd-in" style="--i: {{ $i++ }}">
                            <div class="pd-card pt-store {{ $esActual ? 'actual' : '' }}"
                                style="--c: {{ $col1 }}; --c2: {{ $col2 }}">
                                @if ($esActual)
                                    <span class="pd-tag info pt-here">Activa ahora</span>
                                @endif
                                <div class="pd-card-b d-flex flex-column h-100">
                                    <div class="pt-head">
                                        <span class="pt-badge">{{ $t->codigo }}</span>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="pt-name">{{ $t->nombre }}@if ($t->rubro)
                                                    · {{ $t->rubro }}
                                                @endif
                                            </div>
                                            <div class="pt-city"><i
                                                    class="fas fa-map-marker-alt me-1"></i>{{ $t->ciudad->catalogo_descripcion ?? 'Sin ciudad' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <div class="pt-sales">
                                            <small>Bs</small>{{ \App\Support\Formato::dinero($c['ventas_hoy']) }}
                                        </div>
                                        <div class="meta small text-muted mb-1">Ventas de hoy</div>
                                        <div class="pd-meter"><span
                                                style="width: {{ max(3, $pct) }}%; --c: {{ $col1 }}"></span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="pt-stat"><span><i class="fas fa-box-open me-2"></i>Productos con
                                                stock</span><strong>{{ $c['productos'] }}</strong></div>
                                        <div class="pt-stat"><span><i class="fas fa-bell me-2"></i>Alertas</span>
                                            <span
                                                class="pd-tag {{ $claseAlerta }}">{{ $c['alerta'] ?? 'Sin alertas' }}</span>
                                        </div>
                                        <div class="pt-stat"><span><i
                                                    class="fas fa-user-tie me-2"></i>Administrador</span><strong
                                                class="text-truncate ms-2">{{ $c['admin'] }}</strong></div>
                                    </div>

                                    <form method="POST" action="{{ route('mis_tiendas.entrar', $t) }}" class="mt-auto">
                                        @csrf
                                        <button class="pt-enter">Entrar a la tienda <i
                                                class="fas fa-arrow-right ms-1"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

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
