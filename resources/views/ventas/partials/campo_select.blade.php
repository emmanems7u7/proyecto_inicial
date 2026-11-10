@php
    $crearCatalogo = $crearCatalogo ?? false;
@endphp

<div class="col-md-{{ $col ?? 6 }} mb-3">

    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if (!empty($required))
            <span class="text-danger">*</span>
        @endif
    </label>

    <div class="input-group">

        <select id="{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror"
            @if (!empty($required)) required @endif>
            @if (empty($sinVacio))
                <option value="">{{ $placeholder ?? '-- Seleccione --' }}</option>
            @endif

            @foreach ($options as $key => $texto)
                <option value="{{ $key }}" @selected((string) old($name, $value ?? '') === (string) $key)>
                    {{ $texto }}
                </option>
            @endforeach

        </select>

        @if ($crearCatalogo)
            <button type="button" class="btn btn-outline-primary btn-crear-catalogo" data-select="{{ $name }}"
                data-categoria="{{ $categoriaCatalogo }}" data-prefijo="{{ $prefijoCatalogo ?? 'CAT-' }}"
                data-label="{{ $label }}" title="Agregar {{ strtolower($label) }}">
                <i class="fas fa-plus"></i>
            </button>
        @endif

    </div>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

</div>
