<div class="col-md-{{ $col ?? 6 }} mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }} @if (! empty($required)) <span class="text-danger">*</span> @endif
    </label>
    <select id="{{ $name }}" name="{{ $name }}"
            class="form-select @error($name) is-invalid @enderror"
            @if (! empty($required)) required @endif>
        @if (empty($sinVacio))
            <option value="">{{ $placeholder ?? '-- Seleccione --' }}</option>
        @endif
        @foreach ($options as $key => $texto)
            <option value="{{ $key }}" @selected((string) old($name, $value ?? '') === (string) $key)>{{ $texto }}</option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
