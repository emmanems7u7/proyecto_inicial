@extends('layouts.argon')

@section('content')
    @include('ventas.partials.tabla_estilos')

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h5>Usuarios de la tienda: {{ $tienda->nombre }}</h5>
                    <small><i class="fas fa-info-circle me-1"></i>Marca los usuarios que pueden operar en esta tienda. Los
                        roles admin y administrador_general acceden siempre a todas las tiendas.</small>

                    <div class="mt-3">
                        @include('ventas.partials.alertas')
                    </div>

                    <form method="POST" action="{{ route('tiendas.usuarios.update', $tienda) }}">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-items-center small-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>Usuario</th>
                                        <th>Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($usuarios as $usuario)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input" name="usuarios[]"
                                                    value="{{ $usuario->id }}" id="u{{ $usuario->id }}"
                                                    @checked(in_array($usuario->id, $asignados))>
                                            </td>
                                            <td><label for="u{{ $usuario->id }}"
                                                    class="mb-0">{{ $usuario->nombre_completo }}</label></td>
                                            <td>{{ $usuario->getRoleNames()->implode(', ') ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No hay usuarios en la
                                                empresa.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2 mt-2">
                            <a href="{{ route('tiendas.index') }}" class="btn btn-sm btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save"></i>
                                Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
