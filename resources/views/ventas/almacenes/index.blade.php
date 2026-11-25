@extends('layouts.argon')

@section('content')
<style>
    .al-card { position:relative; background:#fff; border:1px solid #eef0f4; border-radius:18px; box-shadow:0 4px 18px rgba(15,23,42,.06); height:100%; overflow:hidden; transition:transform .18s, box-shadow .18s; }
    .al-card:hover { transform:translateY(-3px); box-shadow:0 12px 26px rgba(15,23,42,.12); }
    .al-card::before { content:""; position:absolute; inset:0 0 auto 0; height:5px; background:var(--c); }
    .al-card.off { opacity:.6; }
    .al-ico { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:1rem; background:var(--c); flex-shrink:0; }
    .al-name { font-weight:800; color:#1f2937; line-height:1.2; }
    .al-desc { font-size:.78rem; color:#6b7280; }
    .al-row { display:flex; justify-content:space-between; padding:.42rem 0; border-bottom:1px dashed #e5e7eb; font-size:.85rem; color:#4b5563; }
    .al-row:last-of-type { border-bottom:0; }
    .al-row strong { color:#111827; }
    .al-ver { font-weight:700; font-size:.82rem; color:var(--c); text-decoration:none; }
    .al-ver:hover { text-decoration:underline; color:var(--c); }
    .al-tag { font-size:.66rem; font-weight:700; padding:.15rem .6rem; border-radius:999px; color:#fff; background:var(--c); }
    .al-panel { background:#fff; border:1px solid #eef0f4; border-radius:18px; box-shadow:0 4px 18px rgba(15,23,42,.06); position:sticky; top:1rem; }
    .al-panel h6 { font-weight:800; }
    .al-panel .form-label { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#6b7280; }
    .al-btn { border:0; border-radius:12px; padding:.65rem 1rem; font-weight:700; color:#fff; width:100%; background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 8px 18px rgba(79,70,229,.3); }
</style>
@php
    $paleta = ['principal' => '#10b981', 'exhibicion' => '#0ea5e9', 'merma' => '#ef4444', 'transito' => '#f59e0b'];
    $iconos = ['principal' => 'fa-warehouse', 'exhibicion' => 'fa-store', 'merma' => 'fa-trash-alt', 'transito' => 'fa-truck'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-0"><i class="fas fa-warehouse me-2 text-primary"></i>Almacenes <small class="text-muted">· {{ $tiendaNombre }}</small></h5>
        <small class="text-muted">Cada almacén pertenece a una tienda y maneja su propio stock. El tipo define su comportamiento.</small>
    </div>
    <div class="d-flex gap-2">
        @if ($tiendas->count() > 1)
            <form method="GET" action="{{ route('almacenes.index') }}">
                <select name="tienda_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($tiendas as $id => $nombre)<option value="{{ $id }}" @selected($id == $tiendaId)>{{ $nombre }}</option>@endforeach
                </select>
            </form>
        @endif
        <a href="{{ route('home') }}" class="btn btn-sm btn-secondary mb-0"><i class="fas fa-arrow-left me-1"></i>Volver</a>
    </div>
</div>
@include('ventas.partials.alertas')

<div class="row g-3">
    <div class="col-xl-8">
        <div class="row g-3">
            @foreach ($almacenes as $a)
                @php
                    $t = \App\Models\Almacen::TIPOS[$a->tipo] ?? \App\Models\Almacen::TIPOS['principal'];
                    $color = $paleta[$a->tipo] ?? '#10b981';
                    $s = $stats[$a->id] ?? null;
                @endphp
                <div class="col-md-6">
                    <div class="al-card {{ $a->estado ? '' : 'off' }}" style="--c: {{ $color }}">
                        <div class="p-3">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <span class="al-ico"><i class="fas {{ $iconos[$a->tipo] ?? 'fa-warehouse' }}"></i></span>
                                <div class="flex-grow-1">
                                    <div class="al-name">{{ $a->codigo }} · {{ $a->nombre }}</div>
                                    <div class="al-desc">{{ $t[1] }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="al-tag">{{ $t[0] }}</span>
                                    @if ($a->es_principal)<div class="mt-1"><span class="badge bg-gradient-info">Predeterminado</span></div>@endif
                                    @unless ($a->estado)<div class="mt-1"><span class="badge bg-gradient-secondary">Inactivo</span></div>@endunless
                                </div>
                            </div>
                            <div class="al-row"><span>Encargado</span><strong>{{ $a->encargado->nombre_completo ?? 'Sin asignar' }}</strong></div>
                            <div class="al-row"><span>Productos</span><strong>{{ number_format($s->productos ?? 0, 0, ',', '.') }}</strong></div>
                            <div class="al-row"><span>Unidades</span><strong>{{ number_format($s->unidades ?? 0, 0, ',', '.') }}</strong></div>
                            @if ($a->ubicaciones)<div class="al-row"><span>Ubicaciones</span><strong class="text-end ms-3" style="max-width:60%">{{ \Illuminate\Support\Str::limit($a->ubicaciones, 60) }}</strong></div>@endif
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                @can('inventario.ver')
                                    <a class="al-ver" href="{{ route('inventario.index', ['almacen_id' => $a->id]) }}">Ver stock <i class="fas fa-arrow-right ms-1"></i></a>
                                @else <span></span> @endcan
                                <div class="text-nowrap">
                                    @can('almacenes.editar')
                                        <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('almacenes.eliminar')
                                        <form action="{{ route('almacenes.destroy', $a) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- En tránsito: no es un almacén físico, resume las transferencias enviadas y no recibidas --}}
            <div class="col-md-6">
                <div class="al-card" style="--c: {{ $paleta['transito'] }}">
                    <div class="p-3">
                        <div class="d-flex align-items-start gap-3 mb-2">
                            <span class="al-ico"><i class="fas fa-truck"></i></span>
                            <div class="flex-grow-1">
                                <div class="al-name">En tránsito</div>
                                <div class="al-desc">Mercadería de transferencias pendientes de recibir</div>
                            </div>
                            <span class="al-tag">Tránsito</span>
                        </div>
                        <div class="al-row"><span>Encargado</span><strong>Automático</strong></div>
                        <div class="al-row"><span>Productos</span><strong>{{ number_format($transito->productos ?? 0, 0, ',', '.') }}</strong></div>
                        <div class="al-row"><span>Unidades</span><strong>{{ number_format($transito->unidades ?? 0, 0, ',', '.') }}</strong></div>
                        @if (Route::has('transferencias.index'))
                            <div class="mt-2"><a class="al-ver" href="{{ route('transferencias.index') }}">Ver transferencias <i class="fas fa-arrow-right ms-1"></i></a></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Nuevo almacén --}}
    <div class="col-xl-4">
        @can('almacenes.crear')
            <div class="al-panel"><div class="p-3">
                <h6 class="mb-3"><i class="fas fa-plus-circle me-1 text-primary"></i> Nuevo almacén</h6>
                <form method="POST" action="{{ route('almacenes.store') }}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="tienda_id" value="{{ $tiendaId }}">
                    <input type="hidden" name="estado" value="1">
                    <div class="mb-2">
                        <label class="form-label" for="n_nombre">Nombre *</label>
                        <input type="text" id="n_nombre" name="nombre" value="{{ old('nombre') }}" class="form-control form-control-sm" required maxlength="120" placeholder="Almacén reserva">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control form-control-sm" value="Automático" disabled>
                        </div>
                        <div class="col-7">
                            <label class="form-label" for="n_tipo">Tipo *</label>
                            <select id="n_tipo" name="tipo" class="form-select form-select-sm" required>
                                @foreach (\App\Models\Almacen::TIPOS as $k => $t)<option value="{{ $k }}" @selected(old('tipo') === $k)>{{ $t[0] }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="n_enc">Encargado</label>
                        <select id="n_enc" name="encargado_id" class="form-select form-select-sm">
                            <option value="">-- Sin asignar --</option>
                            @foreach ($encargados as $id => $nombre)<option value="{{ $id }}" @selected(old('encargado_id') == $id)>{{ $nombre }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="n_ubi">Ubicaciones (racks o pasillos)</label>
                        <input type="text" id="n_ubi" name="ubicaciones" value="{{ old('ubicaciones') }}" class="form-control form-control-sm" maxlength="1000" placeholder="Rack 1-A, Rack 1-B…">
                    </div>
                    <button class="al-btn"><i class="fas fa-save me-1"></i> Guardar almacén</button>
                </form>
                <hr>
                <small class="text-muted d-block"><strong>Principal:</strong> recibe compras y abastece ventas.</small>
                <small class="text-muted d-block"><strong>Exhibición:</strong> productos a la vista.</small>
                <small class="text-muted d-block"><strong>Merma:</strong> dañados o vencidos; no aparece para vender.</small>
                <small class="text-muted d-block"><strong>Tránsito:</strong> se calcula solo con las transferencias pendientes.</small>
                <small class="text-muted d-block mt-2">¿Más datos (estado, dirección, predeterminado)? <a href="{{ route('almacenes.create') }}">Formulario completo</a>.</small>
            </div></div>
        @endcan
    </div>
</div>
@endsection
