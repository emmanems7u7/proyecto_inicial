{{--
    Menú fijo (quemado) del sistema de ventas, compatible con el lateral de Argon.
    Incluir dentro de @if($tiempo_cambio_contraseña != 1), justo DESPUÉS del </ul> de #secciones-list:

        @include('ventas.partials.menu')

    Cuando pases a menús dinámicos, cada @can ya usa el nombre de permiso.
--}}

@isset($tiendaActual)
    <li class="nav-item px-3 mb-2 list-unstyled">
        <form method="POST" action="{{ route('tienda.cambiar') }}">
            @csrf
            <label class="text-xs text-black mb-1"><i class="fas fa-store me-1"></i> Tienda activa</label>
            <select name="tienda_id" class="form-select form-select-sm" onchange="this.form.submit()"
                    @if ($tiendasPermitidas->count() < 2) disabled @endif>
                @foreach ($tiendasPermitidas as $t)
                    <option value="{{ $t->id }}" @selected($t->id === $tiendaActual->id)>{{ $t->nombre }}</option>
                @endforeach
            </select>
        </form>
    </li>
@endisset

<ul id="menu-sistema-ventas" class="list-unstyled">

    @canany(['empresa.ver', 'tiendas.ver', 'almacenes.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-store me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Estructura</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('empresa.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('empresa.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('empresa.edit') }}">
                            <span class="text-black nav-link-text">Mi empresa</span>
                        </a>
                    </li>
                @endcan

                @can('tiendas.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('tiendas.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('tiendas.index') }}">
                            <span class="text-black nav-link-text">Tiendas / Sucursales</span>
                        </a>
                    </li>
                @endcan

                @can('almacenes.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('almacenes.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('almacenes.index') }}">
                            <span class="text-black nav-link-text">Almacenes</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['categorias_producto.ver', 'productos.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-box-open me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Productos</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('categorias_producto.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('categorias_producto.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('categorias_producto.index') }}">
                            <span class="text-black nav-link-text">Categorías de producto</span>
                        </a>
                    </li>
                @endcan

                @can('productos.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('productos.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('productos.index') }}">
                            <span class="text-black nav-link-text">Productos</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['inventario.ver', 'inventario.movimientos', 'kardex.ver', 'lotes.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-warehouse me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Inventario</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('inventario.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('inventario.index', 'inventario.config.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('inventario.index') }}">
                            <span class="text-black nav-link-text">Stock por almacén</span>
                        </a>
                    </li>
                @endcan

                @can('inventario.movimientos')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('inventario.movimiento.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('inventario.movimiento.create') }}">
                            <span class="text-black nav-link-text">Registrar movimiento</span>
                        </a>
                    </li>
                @endcan

                @can('kardex.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('inventario.kardex') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('inventario.kardex') }}">
                            <span class="text-black nav-link-text">Kardex</span>
                        </a>
                    </li>
                @endcan

                @can('lotes.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('inventario.lotes') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('inventario.lotes') }}">
                            <span class="text-black nav-link-text">Lotes y vencimientos</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['clientes.ver', 'proveedores.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-address-book me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Personas</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('clientes.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('clientes.index') }}">
                            <span class="text-black nav-link-text">Clientes</span>
                        </a>
                    </li>
                @endcan

                @can('proveedores.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('proveedores.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('proveedores.index') }}">
                            <span class="text-black nav-link-text">Proveedores</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany
</ul>
