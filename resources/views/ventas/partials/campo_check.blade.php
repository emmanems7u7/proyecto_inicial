<div class="col-md-{{ $col ?? 6 }} mb-3 d-flex align-items-end">
    <div class="form-check form-switch">
        <input type="hidden" name="{{ $name }}" value="0">
        <input class="form-check-input" type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1"
               @checked(old($name, $value ?? false))>
        <label class="form-check-label" for="{{ $name }}">{{ $label }}</label>
    </div>
</div>
