@extends('layouts.argon')

@section('content')

@php($hechos = collect($pasos)->where('listo', true)->count())

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-lg mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">{{ $tienda->nombre }} <small class="text-muted">· {{ $tienda->codigo }}</small></h5>
                        <small class="text-muted">Completa los pasos para dejar la tienda lista para vender.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tiendas.edit', $tienda) }}" class="btn btn-sm btn-outline-secondary">Editar datos</a>
                        <a href="{{ route('tiendas.index') }}" class="btn btn-sm btn-secondary">Volver</a>
                    </div>
                </div>
                <div class="progress mt-3" style="height:8px;"><div class="progress-bar bg-gradient-success" style="width: {{ round($hechos / count($pasos) * 100) }}%"></div></div>
                <small class="text-muted">{{ $hechos }} de {{ count($pasos) }} pasos completos</small>
                <div class="mt-2">@include('ventas.partials.alertas')</div>
            </div>
        </div>

        {{-- Paso 2: almacén principal --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h6>2. Almacén principal @if ($pasos[1]['listo'])<span class="badge bg-gradient-success">Listo</span>@endif</h6>
            @if ($principal)
                <small>{{ $principal->codigo }} · {{ $principal->nombre }}. Recibe las compras y abastece las ventas.</small>
            @endif
            @can('almacenes.ver')<div class="mt-2"><a href="{{ route('almacenes.index') }}" class="btn btn-sm btn-outline-primary mb-0">Ver / agregar almacenes</a></div>@endcan
        </div></div>

        {{-- Paso 3: administrador --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h6>3. Administrador de la tienda @if ($pasos[2]['listo'])<span class="badge bg-gradient-success">Listo</span>@endif</h6>
            @foreach ($admins as $a)<div><small><i class="fas fa-user me-1"></i>{{ $a->nombre_completo }} · {{ $a->email }}</small></div>@endforeach
            @can('tiendas.usuarios')
                <ul class="nav nav-pills my-2" role="tablist">
                    <li class="nav-item"><button class="nav-link active py-1 px-3" data-bs-toggle="pill" data-bs-target="#adm-nuevo" type="button">Crear usuario nuevo</button></li>
                    <li class="nav-item"><button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#adm-existente" type="button">Usar un usuario existente</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="adm-nuevo">
                        <form method="POST" action="{{ route('tiendas.configuracion.administrador', $tienda) }}" autocomplete="off">
                            @csrf <input type="hidden" name="modo" value="nuevo">
                            <div class="row">
                                @include('ventas.partials.campo_texto', ['name' => 'usuario_nombres', 'label' => 'Nombres', 'col' => 4, 'required' => true, 'maxlength' => 120])
                                @include('ventas.partials.campo_texto', ['name' => 'usuario_app', 'label' => 'Apellido paterno', 'col' => 4, 'required' => true, 'maxlength' => 80])
                                @include('ventas.partials.campo_texto', ['name' => 'usuario_apm', 'label' => 'Apellido materno', 'col' => 4, 'maxlength' => 80])
                                @include('ventas.partials.campo_texto', ['name' => 'email', 'label' => 'Correo', 'col' => 4, 'type' => 'email', 'required' => true, 'maxlength' => 150])
                                @include('ventas.partials.campo_texto', ['name' => 'password', 'label' => 'Contraseña', 'col' => 4, 'type' => 'password', 'required' => true])
                                @include('ventas.partials.campo_texto', ['name' => 'password_confirmation', 'label' => 'Repetir contraseña', 'col' => 4, 'type' => 'password', 'required' => true])
                            </div>
                            <button class="btn btn-sm btn-primary mb-0"><i class="fas fa-user-plus"></i> Crear y asignar como administrador</button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="adm-existente">
                        <form method="POST" action="{{ route('tiendas.configuracion.administrador', $tienda) }}">
                            @csrf <input type="hidden" name="modo" value="existente">
                            <div class="row">
                                @include('ventas.partials.campo_select', ['name' => 'user_id', 'label' => 'Usuario', 'col' => 8, 'options' => $usuarios, 'required' => true])
                            </div>
                            <button class="btn btn-sm btn-primary mb-0"><i class="fas fa-user-check"></i> Asignar como administrador</button>
                        </form>
                    </div>
                </div>
            @endcan
        </div></div>

        {{-- Paso 4: caja --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h6>4. Caja @if ($pasos[3]['listo'])<span class="badge bg-gradient-success">Listo</span>@endif</h6>
            @foreach ($cajas as $c)<div><small><i class="fas fa-cash-register me-1"></i>{{ $c->codigo }} · {{ $c->nombre }}</small></div>@endforeach
            @can('cajas.crear')
                <form method="POST" action="{{ route('tiendas.configuracion.caja', $tienda) }}" class="mt-2" autocomplete="off">
                    @csrf
                    <div class="row">
                        @include('ventas.partials.campo_texto', ['name' => 'codigo', 'label' => 'Código', 'col' => 4, 'value' => $codigoCaja, 'required' => true, 'maxlength' => 20])
                        @include('ventas.partials.campo_texto', ['name' => 'nombre', 'label' => 'Nombre', 'col' => 8, 'value' => 'Caja principal', 'required' => true, 'maxlength' => 120])
                    </div>
                    <button class="btn btn-sm btn-primary mb-0"><i class="fas fa-plus"></i> Crear caja</button>
                </form>
            @endcan
        </div></div>

        {{-- Paso 5: productos --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h6>5. Productos @if ($pasos[4]['listo'])<span class="badge bg-gradient-success">Listo</span>@endif</h6>
            <small>El catálogo es de toda la empresa: ya hay <strong>{{ $totalProductos }}</strong> producto(s) disponibles para esta tienda. Cada tienda maneja su propio stock por almacén.</small>
            @can('productos.ver')<div class="mt-2"><a href="{{ route('productos.index') }}" class="btn btn-sm btn-outline-primary mb-0">Ver / agregar productos</a></div>@endcan
        </div></div>

        {{-- Paso 6: stock inicial --}}
        <div class="card shadow-sm mb-3"><div class="card-body">
            <h6>6. Stock inicial @if ($pasos[5]['listo'])<span class="badge bg-gradient-success">Listo</span>@endif</h6>
            <small>Registra una <strong>Entrada</strong> (motivo “Stock inicial”) por cada producto en el almacén principal de esta tienda.</small>
            @can('inventario.movimientos')
                @if ($principal)<div class="mt-2"><a href="{{ route('inventario.movimiento.create', ['almacen_id' => $principal->id]) }}" class="btn btn-sm btn-outline-primary mb-0">Cargar stock inicial</a></div>@endif
            @endcan
        </div></div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-lg">
            <div class="card-body">
                <h6>Para dejarla lista</h6>
                @foreach ($pasos as $p)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <span><strong>{{ $p['n'] }}</strong> {{ $p['titulo'] }}</span>
                        @if ($p['listo'])<span class="badge bg-gradient-success">Listo</span>
                        @elseif ($p['n'] === $actual)<span class="badge bg-gradient-warning">En curso</span>
                        @else<span class="badge bg-gradient-secondary">Pendiente</span>@endif
                    </div>
                @endforeach
                @if ($actual === null)
                    <div class="alert alert-success text-white py-2 mt-3 mb-0">¡La tienda está lista! Ya puedes entrar a ella desde “Mis tiendas”.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
