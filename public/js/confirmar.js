// Pide confirmación antes de enviar los formularios marcados con data-confirmar, como el de
// eliminar una auditoría. Sin JavaScript el formulario se envía igual, sin la pregunta.
(function () {
    document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!window.confirm(formulario.dataset.confirmar)) {
                evento.preventDefault();
                return;
            }

            // Evita enviar dos veces la misma eliminación con un doble clic.
            formulario.querySelector('[type="submit"]').disabled = true;
        });
    });
})();
