@php
    $id = $name;
    $fotoActual = $foto ?? null;
@endphp

<div class="col-md-{{ $col ?? 12 }} mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label ?? 'Fotografía' }}</label>

    <div class="d-flex align-items-center gap-3 flex-wrap">

        {{-- Enlace para visualizar la foto existente --}}
        <div id="{{ $id }}_actual" class="{{ $fotoActual ? '' : 'd-none' }}">
            <a href="javascript:void(0)" class="text-primary text-decoration-none" data-bs-toggle="popover"
                data-bs-trigger="hover focus" data-bs-html="true" data-bs-placement="right"
                data-bs-content="<img src='{{ $fotoActual ? asset($fotoActual) : '' }}' style='max-width:220px; max-height:220px; object-fit:contain;'>">
                <i class="fas fa-eye"></i> Ver Foto
            </a>
        </div>

        {{-- Previsualización de la nueva foto --}}
        <div id="{{ $id }}_preview_container" class="d-none align-items-center gap-2">
            <a href="javascript:void(0)" id="{{ $id }}_preview_link" class="text-primary text-decoration-none"
                data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true" data-bs-placement="right">
                <i class="fas fa-eye"></i> Ver Foto
            </a>

            <button type="button" class="btn btn-xs btn-outline-danger" id="{{ $id }}_quitar"
                title="Quitar fotografía">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <input type="file" class="form-control" style="max-width:300px" id="{{ $id }}"
            name="{{ $name }}" accept="image/jpeg,image/png,image/webp">

        {{-- Indicador de eliminación de la foto existente --}}
        <input type="hidden" name="eliminar_{{ $name }}" id="{{ $id }}_eliminar" value="0">

    </div>

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const input = document.getElementById('{{ $id }}');
        const preview = document.getElementById('{{ $id }}_preview_container');
        const previewLink = document.getElementById('{{ $id }}_preview_link');
        const quitar = document.getElementById('{{ $id }}_quitar');
        const actual = document.getElementById('{{ $id }}_actual');
        const eliminar = document.getElementById('{{ $id }}_eliminar');

        let objectUrl = null;

        function inicializarPopover(elemento) {
            bootstrap.Popover.getInstance(elemento)?.dispose();

            new bootstrap.Popover(elemento, {
                container: 'body',
                html: true,
                sanitize: false
            });
        }

        actual.querySelector('[data-bs-toggle="popover"]') &&
            inicializarPopover(actual.querySelector('[data-bs-toggle="popover"]'));

        input.addEventListener('change', function() {

            const archivo = this.files[0];

            if (!archivo) return;

            if (!archivo.type.startsWith('image/')) {
                this.value = '';
                return;
            }

            if (objectUrl) URL.revokeObjectURL(objectUrl);

            objectUrl = URL.createObjectURL(archivo);

            previewLink.setAttribute(
                'data-bs-content',
                `<img src="${objectUrl}" style="max-width:220px;max-height:220px;object-fit:contain;">`
            );

            inicializarPopover(previewLink);

            preview.classList.remove('d-none');
            preview.classList.add('d-flex');

            actual.classList.add('d-none');
            eliminar.value = '0';
        });

        quitar.addEventListener('click', function() {

            input.value = '';

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            bootstrap.Popover.getInstance(previewLink)?.dispose();

            preview.classList.add('d-none');
            preview.classList.remove('d-flex');

            // Mostrar nuevamente la fotografía existente
            actual.classList.toggle('d-none', !{{ $fotoActual ? 'false' : 'true' }});

            // Solo eliminar la fotografía existente si se indicó explícitamente
            eliminar.value = '0';
        });

    });
</script>
