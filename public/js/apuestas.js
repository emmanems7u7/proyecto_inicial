
function copiarCodigo() {

    let codigo = document.getElementById("codigoApuestaFinal").innerText;

    navigator.clipboard.writeText(codigo).then(() => {
        mostrarAlerta("success", "Código copiado al portapapeles");
    });
}
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.btn-apostar')
        .forEach(btn => {

            btn.addEventListener('click', function() {

                document.getElementById('partido_id').value =
                    this.dataset.id;





                document.getElementById('local_nombre_pronostico').innerText =
                    this.dataset.local;

                document.getElementById('visitante_nombre_pronostico').innerText =
                    this.dataset.visitante;

                document.getElementById('local_logo_pronostico').src =
                    this.dataset.localLogo;

                document.getElementById('visitante_logo_pronostico').src =
                    this.dataset.visitanteLogo;


                new bootstrap.Modal(
                    document.getElementById('modalApuesta')
                ).show();

            });

        });


    document.getElementById('comprobante').addEventListener('change', function(e) {

        const file = e.target.files[0];

        if (!file) {
            document.getElementById('contenedorPreview')
                .classList.add('d-none');
            return;
        }

        const url = URL.createObjectURL(file);

        const link = document.getElementById('previewComprobanteLink');

        link.href = url;

        document.getElementById('contenedorPreview')
            .classList.remove('d-none');

    });


    function limpiarErrores() {
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
    }

    function mostrarErrores(errors) {

        limpiarErrores();

        let mensajes = [];

        Object.keys(errors).forEach(campo => {

            let input = document.querySelector(`[name="${campo}"]`);

            if (input) {
                input.classList.add('is-invalid');
            }

            mensajes.push(errors[campo][0]);
        });

        // mostrar resumen en alertify
        mostrarAlerta('error', mensajes.join('<br>'));
    }

    document.getElementById('formApuesta').addEventListener('submit', function(e) {

        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        limpiarErrores();

        fetch(form.action, {
                method: "POST",
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {

                let data = await res.json();

                if (!res.ok) {

                    if (data.errors) {
                        mostrarErrores(data.errors);
                        return;
                    }

                    mostrarAlerta('error', 'esperado');
                    return;
                }

                if (data.status) {

                    bootstrap.Modal.getInstance(document.getElementById('modalApuesta'))
                        .hide();

                    mostrarExitoApuesta(data.codigo_apuesta);

                }

                form.reset();

            })
            .catch(() => {
                mostrarAlerta('error', 'Error de conexión');
            });

    });
});

function mostrarExitoApuesta(codigo) {

    document.getElementById("codigoApuestaFinal").innerText = codigo;

    let modal = new bootstrap.Modal(document.getElementById('modalExitoApuesta'));
    modal.show();
}



function mostrarExitoApuesta(codigo) {

    document.getElementById("codigoApuestaFinal").innerText = codigo;

    let modal = new bootstrap.Modal(document.getElementById('modalExitoApuesta'));
    modal.show();
}

document.getElementById('modalExitoApuesta')
    .addEventListener('hidden.bs.modal', function() {
        location.reload();
    });