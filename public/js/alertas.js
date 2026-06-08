alertify.defaults.theme.ok = "btn btn-danger";
alertify.defaults.theme.cancel = "btn btn-secondary";
alertify.defaults.theme.input = "form-control";
alertify.defaults.glossary.title = "Confirmar acción";
alertify.defaults.transition = "zoom";

function confirmarEliminacion(
    formId,
    mensaje = '¿Estás seguro de que deseas eliminar este elemento?',
    callback = null
) {
    alertify.confirm(
        'Confirmar acción',
        mensaje,
        function() {

            if (callback) {
                callback();
                return;
            }

            document.getElementById(formId).submit();
        },
        function() {
            alertify.error('Acción cancelada');
        }
    ).set('labels', {
        ok: 'Aceptar',
        cancel: 'Cancelar'
    });
}


function mostrarAlerta(tipo, mensaje, opciones = {}) {

    switch (tipo) {

        case 'success':
            alertify.success(mensaje);
        break;

        case 'error':
            alertify.error(mensaje);
        break;

        case 'warning':
            alertify.warning(mensaje);
        break;

        case 'confirm':
            alertify.confirm(
                opciones.titulo || 'Confirmar',
                mensaje,
                function () {
                    if (typeof opciones.onOk === 'function') {
                        opciones.onOk();
                    }
                },
                function () {
                    if (typeof opciones.onCancel === 'function') {
                        opciones.onCancel();
                    } else {
                        alertify.error('Acción cancelada');
                    }
                }
            );
        break;

        default:
            console.warn('Tipo de alerta no soportado');
    }
}

