<div class="col-md-{{ $col ?? 12 }} mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 2 }}"
              class="form-control @error($name) is-invalid @enderror"
              @isset($maxlength) maxlength="{{ $maxlength }}" @endisset>{{ old($name, $value ?? '') }}</textarea>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
