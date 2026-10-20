@php($type = $type ?? 'text')
<div class="col-md-{{ $col ?? 6 }} mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }} @if (! empty($required)) <span class="text-danger">*</span> @endif
    </label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
           class="form-control @error($name) is-invalid @enderror"
           value="{{ old($name, $value ?? '') }}"
           @if (! empty($required)) required @endif
           @isset($step) step="{{ $step }}" @endisset
           @isset($min) min="{{ $min }}" @endisset
           @isset($maxlength) maxlength="{{ $maxlength }}" @endisset
           @isset($placeholder) placeholder="{{ $placeholder }}" @endisset>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
