@extends('layouts.argon')

@section('content')

@include('ventas.partials.tabla_estilos')

<div class="row">
    <div class="col-md-6">
        <div class="card shadow-lg">
            <div class="card-body">
                <h5>Administración de Clientes</h5>

                <a href="{{ route('home') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
                @can('clientes.crear')
                    <a href="{{ route('clientes.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Nuevo cliente</a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <small><i class="fas fa-info-circle me-1"></i>Los clientes son de toda la empresa; el estado de cuenta puede verse por tienda o global.</small><br>
                <small><i class="fas fa-credit-card me-1"></i>Define límite y días de crédito para habilitar ventas a crédito (fase siguiente).</small><br>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <div class="card">
            <div class="card-body">

                @include('ventas.partials.alertas')

                <form method="GET" action="{{ route('clientes.index') }}" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Buscar por nombre, documento o teléfono...">
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos los estados</option>
                            <option value="1" @selected(request('estado') === '1')>Activos</option>
                            <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-info"><i class="fas fa-search"></i> Buscar</button>
                        <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-items-center small-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Límite crédito</th>
                                <th>Días</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->numero_documento ?: '—' }}{{ $cliente->complemento ? '-' . $cliente->complemento : '' }}</td>
                                    <td>{{ $cliente->nombre }}</td>
                                    <td>{{ $cliente->telefono ?: '—' }}</td>
                                    <td>{{ $cliente->email ?: '—' }}</td>
                                    <td class="text-end">{{ \App\Support\Formato::dinero($cliente->limite_credito) }}</td>
                                    <td class="text-center">{{ $cliente->dias_credito }}</td>
                                    <td class="text-center"><span class="badge bg-gradient-{{ $cliente->estado ? 'success' : 'secondary' }}">{{ $cliente->estado ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td class="text-end text-nowrap">

                                        @can('clientes.editar')
                                            <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-xs btn-warning mb-0 px-2 py-1" title="Editar"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        @can('clientes.eliminar')
                                            <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Eliminar este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-xs btn-danger mb-0 px-2 py-1" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-3">No se encontraron registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-2">{{ $clientes->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
