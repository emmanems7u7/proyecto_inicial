<div class="row">
    @include('ventas.partials.campo_check', ['name' => 'activo', 'label' => 'Automatización activa', 'col' => 6, 'value' => $automatizacion->activo])

    @if ($automatizacion->frecuencia === 'diaria')
        @include('ventas.partials.campo_texto', ['name' => 'hora_ejecucion', 'label' => 'Hora de ejecución', 'col' => 3, 'type' => 'time', 'value' => substr((string) $automatizacion->hora_ejecucion, 0, 5)])
    @endif
</div>

@if ($esquema)
    <hr>
    <h6>Parámetros</h6>
    <div class="row">
        @foreach ($esquema as $clave => [$etiqueta, $tipo, $defecto])
            @php $valor = old('parametros.' . $clave, $automatizacion->parametro($clave, $defecto)); @endphp
            @if ($tipo === 'bool')
                <div class="col-12 mb-3">
                    <div class="form-check form-switch">
                        <input type="hidden" name="parametros[{{ $clave }}]" value="0">
                        <input class="form-check-input" type="checkbox" id="p_{{ $clave }}" name="parametros[{{ $clave }}]" value="1" @checked($valor)>
                        <label class="form-check-label" for="p_{{ $clave }}">{{ $etiqueta }}</label>
                    </div>
                </div>
            @else
                <div class="col-md-{{ $tipo === 'int' ? 4 : 12 }} mb-3">
                    <label for="p_{{ $clave }}" class="form-label">{{ $etiqueta }}</label>
                    <input type="{{ $tipo === 'int' ? 'number' : 'text' }}" @if ($tipo === 'int') min="0" @endif
                           id="p_{{ $clave }}" name="parametros[{{ $clave }}]" value="{{ $valor }}" class="form-control">
                </div>
            @endif
        @endforeach
    </div>
@endif
