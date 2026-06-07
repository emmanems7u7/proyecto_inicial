@extends('layouts.argon')

@section('content')

    <div class="card shadow-lg mx-4 card-profile-bottom">
        <div class="card-body p-3">
            <p>Configuración de Seguridad: Reglas de Credenciales</p>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">

                    <div class="card-body">
                        <form method="POST" action="{{ route('configuracion.credenciales.actualizar') }}">
                            @csrf

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="conf_long_min" class="form-label">Longitud Mínima</label>
                                    <input type="number" name="conf_long_min" class="form-control"
                                        value="{{ old('conf_long_min', $configuracion->conf_long_min) }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="conf_long_max" class="form-label">Longitud Máxima</label>
                                    <input type="number" name="conf_long_max" class="form-control"
                                        value="{{ old('conf_long_max', $configuracion->conf_long_max) }}" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="conf_tiempo_bloqueo" class="form-label">Tiempo de Bloqueo (seg)</label>
                                    <input type="number" name="conf_tiempo_bloqueo" class="form-control"
                                        value="{{ old('conf_tiempo_bloqueo', $configuracion->conf_tiempo_bloqueo) }}"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="conf_duracion_min" class="form-label">Duración Mínima (días)</label>
                                    <input type="number" name="conf_duracion_min" class="form-control"
                                        value="{{ old('conf_duracion_min', $configuracion->conf_duracion_min) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="conf_duracion_max" class="form-label">Duración Máxima (días)</label>
                                    <input type="number" name="conf_duracion_max" class="form-control"
                                        value="{{ old('conf_duracion_max', $configuracion->conf_duracion_max) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="conf_defecto" class="form-label">Contraseña por Defecto</label>
                                    <input type="text" name="conf_defecto" class="form-control"
                                        value="{{ old('conf_defecto', $configuracion->conf_defecto) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="conf_req_upper" class="form-label">¿Requiere Mayúsculas?</label>
                                    <select name="conf_req_upper" class="form-control">
                                        <option value="1" {{ $configuracion->conf_req_upper ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ !$configuracion->conf_req_upper ? 'selected' : '' }}>No
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="conf_req_num" class="form-label">¿Requiere Números?</label>
                                    <select name="conf_req_num" class="form-control">
                                        <option value="1" {{ $configuracion->conf_req_num ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ !$configuracion->conf_req_num ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="conf_req_esp" class="form-label">¿Requiere Caracteres Especiales?</label>
                                    <select name="conf_req_esp" class="form-control">
                                        <option value="1" {{ $configuracion->conf_req_esp ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ !$configuracion->conf_req_esp ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            @can('configuracion.actualizar')
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            @endcan

                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection