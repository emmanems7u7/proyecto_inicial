{{--
    Menú fijo (quemado) de la fase 2. Incluir justo después del menú de la fase 1:

        @include('ventas.partials.menu')
        @include('ventas.partials.menu_fase2')
--}}

<ul id="menu-sistema-ventas-2" class="list-unstyled">

    @canany(['panel.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-chart-pie me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Panel</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('panel.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('panel.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('panel.index') }}">
                            <span class="text-black nav-link-text">Panel de control</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['ventas.ver', 'ventas.crear', 'cotizaciones.ver', 'cxc.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-cash-register me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Ventas</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('ventas.crear')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('ventas.create') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('ventas.create') }}">
                            <span class="text-black nav-link-text">Nueva venta</span>
                        </a>
                    </li>
                @endcan

                @can('ventas.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('ventas.index', 'ventas.show', 'ventas.devolucion.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('ventas.index') }}">
                            <span class="text-black nav-link-text">Ventas y pedidos</span>
                        </a>
                    </li>
                @endcan

                @can('cotizaciones.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('cotizaciones.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('cotizaciones.index') }}">
                            <span class="text-black nav-link-text">Cotizaciones</span>
                        </a>
                    </li>
                @endcan

                @can('cxc.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('cxc.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('cxc.index') }}">
                            <span class="text-black nav-link-text">Cuentas por cobrar</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['caja.ver', 'caja.abrir', 'cajas.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-money-bill-wave me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Caja</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('caja.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('caja.sesiones.*', 'caja.cerrar.*', 'caja.movimiento.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('caja.sesiones.index') }}">
                            <span class="text-black nav-link-text">Mi caja / Sesiones</span>
                        </a>
                    </li>
                @endcan

                @can('caja.abrir')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('caja.abrir.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('caja.abrir.create') }}">
                            <span class="text-black nav-link-text">Abrir caja</span>
                        </a>
                    </li>
                @endcan

                @can('cajas.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('cajas.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('cajas.index') }}">
                            <span class="text-black nav-link-text">Configurar cajas</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['compras.ver', 'ordenes_compra.ver', 'cxp.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-truck-loading me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Compras</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('ordenes_compra.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('ordenes_compra.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('ordenes_compra.index') }}">
                            <span class="text-black nav-link-text">Órdenes de compra</span>
                        </a>
                    </li>
                @endcan

                @can('compras.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('compras.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('compras.index') }}">
                            <span class="text-black nav-link-text">Compras</span>
                        </a>
                    </li>
                @endcan

                @can('cxp.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('cxp.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('cxp.index') }}">
                            <span class="text-black nav-link-text">Cuentas por pagar</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['transferencias.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-exchange-alt me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Traslados</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('transferencias.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('transferencias.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('transferencias.index') }}">
                            <span class="text-black nav-link-text">Transferencias</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['reportes.ventas', 'reportes.inventario', 'reportes.compras', 'reportes.cuentas', 'reportes.caja', 'reportes.globales', 'reportes.auditoria'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-chart-bar me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Reportes</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('reportes.ventas')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.ventas') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.ventas') }}">
                            <span class="text-black nav-link-text">Ventas</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.inventario')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.inventario') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.inventario') }}">
                            <span class="text-black nav-link-text">Inventario</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.compras')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.compras') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.compras') }}">
                            <span class="text-black nav-link-text">Compras</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.cuentas')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.cuentas') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.cuentas') }}">
                            <span class="text-black nav-link-text">Cuentas y vencimientos</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.caja')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.caja') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.caja') }}">
                            <span class="text-black nav-link-text">Caja</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.globales')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.globales') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.globales') }}">
                            <span class="text-black nav-link-text">Globales por tienda</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.auditoria')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.auditoria') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.auditoria') }}">
                            <span class="text-black nav-link-text">Auditoría</span>
                        </a>
                    </li>
                @endcan

                @can('reportes.auditoria')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('reportes.accesos') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('reportes.accesos') }}">
                            <span class="text-black nav-link-text">Historial de accesos</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @canany(['automatizaciones.ver', 'correos.ver'])
        <li class="seccion-item p-2 text-black">
            <div class="text-black d-flex align-items-center">
                <i class="fas fa-robot me-2"></i>
                <h6 class="text-black m-0 text-uppercase text-xs font-weight-bolder">Automatización</h6>
            </div>
            <ul class="list-unstyled ms-2">
                @can('automatizaciones.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('automatizaciones.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('automatizaciones.index') }}">
                            <span class="text-black nav-link-text">Automatizaciones</span>
                        </a>
                    </li>
                @endcan

                @can('correos.ver')
                    <li class="nav-item text-black">
                        <a class="nav-link {{ request()->routeIs('correos.*') ? 'active bg-gradient-' . ($color ?? 'primary') : '' }}" href="{{ route('correos.index') }}">
                            <span class="text-black nav-link-text">Cola de correos</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany
</ul>
