@extends('layouts.argon')

@section('content')
    <style>
        .ct-card {
            background: #fff;
            border: 1px solid #eef0f4;
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
        }

        .ct-th {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            object-fit: cover;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .72rem;
            color: rgba(31, 41, 55, .85);
        }

        .ct-chip {
            display: inline-block;
            font-size: .66rem;
            font-weight: 600;
            padding: .1rem .5rem;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            margin: 0 .2rem .2rem 0;
        }

        .ct-chip.pop {
            background: #fef3c7;
            color: #92400e;
        }

        .ct-tabla th {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6b7280;
            border-bottom: 1px solid #eef0f4;
        }

        .ct-tabla td {
            vertical-align: middle;
            font-size: .85rem;
        }

        .ct-tabla tbody tr {
            transition: background .12s;
        }

        .ct-tabla tbody tr:hover {
            background: #f8f9ff;
        }

        .ct-tabla tbody tr.sel {
            background: #eef2ff;
        }

        .ct-stock {
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
        }

        .ct-stock .cero {
            color: #ef4444;
        }
    </style>
    @php($colores = ['#F7D56E', '#D9C7A0', '#E8B44F', '#F0C987', '#A8D5A2', '#C9B6E4', '#92C3E4'])

    <div class="ct-card mb-3">
        <div class="p-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Catálogo de productos</h5>
                    <small class="text-muted">El producto es global para toda la empresa; el stock se controla por almacén.
                        Cada producto tiene sus presentaciones (unidad, media caja, caja…).</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary mb-0"><i
                            class="fas fa-arrow-left me-1"></i>Volver</a>
                    @can('productos.crear')
                        <a href="{{ route('productos.create') }}" class="btn btn-sm btn-primary mb-0"><i
                                class="fas fa-plus"></i> Nuevo producto</a>
                    @endcan
                </div>
            </div>

            @include('ventas.partials.alertas')

            <form method="GET" action="{{ route('productos.index') }}" class="row g-2">
                <div class="col-lg-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="q" value="{{ $q }}" class="form-control"
                            placeholder="Buscar por nombre, SKU, código, marca o nombre popular…">
                    </div>
                </div>
                <div class="col-lg-2">
                    <select name="almacen_id" class="form-select form-select-sm">
                        <option value="">Almacén: todos</option>
                        @foreach ($almacenes as $a)
                            <option value="{{ $a->id }}" @selected(request('almacen_id') == $a->id)>{{ $a->codigo }} ·
                                {{ $a->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <select name="categoria_producto_id" class="form-select form-select-sm">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $id => $nombre)
                            <option value="{{ $id }}" @selected((string) request('categoria_producto_id') === (string) $id)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1">
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activos</option>
                        <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-info mb-0"><i class="fas fa-search"></i> Buscar</button>
                    <a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-secondary mb-0">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="table-responsive px-3 pb-2">
            <table class="table ct-tabla align-items-center mb-0">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Producto</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th>Vencimiento</th>
                        <th>Stock {{ $columnas->pluck('codigo')->implode(' / ') }}</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productos as $p)
                        <tr class="{{ $editar && $editar->id === $p->id ? 'sel' : '' }}">
                            <td>
                                @if ($p->imagen)
                                    <img class="ct-th"
                                        src="{{ str_starts_with($p->imagen, 'http') ? $p->imagen : asset(ltrim($p->imagen, '/')) }}"
                                        alt="{{ $p->nombre }}">
                                @else
                                    <span class="ct-th"
                                        style="background:linear-gradient(135deg,{{ $colores[$p->id % 7] }},{{ $colores[($p->id + 3) % 7] }})">{{ strtoupper(mb_substr($p->nombre, 0, 3)) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $p->nombre }}</div>
                                <div class="text-muted small">{{ $p->codigo }}@if ($p->codigo_barras)
                                        · {{ $p->codigo_barras }}
                                    @endif
                                </div>
                                <div class="mt-1">
                                    @foreach ($p->presentaciones as $pr)
                                        <span class="ct-chip">{{ $pr->nombre }}@if ($pr->unidades != 1)
                                                · {{ $pr->unidades + 0 }} u.
                                            @endif
                                        </span>
                                        @if ($pr->nombre_popular)
                                            <span class="ct-chip pop">“{{ $pr->nombre_popular }}”</span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td>{{ $p->marca ?: '—' }}</td>
                            <td>{{ $p->categoria->nombre ?? '—' }}</td>
                            <td>
                                @if ($p->maneja_vencimiento)
                                    <span class="badge bg-gradient-success">Controlado</span>
                                @elseif ($p->maneja_lotes)
                                    <span class="badge bg-gradient-info">Lotes</span>
                                @else<span class="badge bg-gradient-secondary">No aplica</span>
                                @endif
                            </td>
                            <td class="ct-stock">
                                @foreach ($columnas as $c)
                                    @php($cant = (float) ($stock[$p->id][$c->id] ?? 0))
                                    <span
                                        class="{{ $cant <= 0 ? 'cero' : '' }}">{{ \App\Support\Formato::cantidad($cant) }}</span>
                                    @if (!$loop->last)
                                        /
                                    @endif
                                @endforeach
                            </td>
                            <td><span
                                    class="badge bg-gradient-{{ $p->estado ? 'success' : 'secondary' }}">{{ $p->estado ? 'Activo' : 'Inactivo' }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                @can('productos.editar')
                                    <a href="{{ route('productos.index', array_merge(request()->except('editar', 'page'), ['editar' => $p->id])) }}#editor"
                                        class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i
                                            class="fas fa-edit"></i></a>
                                @endcan
                                @can('productos.eliminar')
                                    <form action="{{ route('productos.destroy', $p) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este registro?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No se encontraron productos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-3 pb-3">{{ $productos->links() }}</div>
        </div>

        @if ($editar)
            <div id="editor" class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i>Editar: {{ $editar->nombre }}</h5>
                    <a href="{{ route('productos.index', request()->except('editar')) }}"
                        class="btn btn-sm btn-outline-secondary mb-0">Cerrar</a>
                </div>
                @include('ventas.productos._form', [
                    'producto' => $editar,
                    'accion' => route('productos.update', $editar),
                    'metodo' => 'PUT',
                    'cancelar' => route('productos.index', request()->except('editar')),
                    'conOtro' => false,
                ])
            </div>
            <script>
                document.getElementById('editor').scrollIntoView({
                    behavior: 'smooth'
                });
            </script>
        @endif
    @endsection
