// Validación en el cliente del formulario de auditorías. Es una ayuda para el usuario:
// la validación que garantiza los datos está en el servidor.
(function () {
    const formulario = document.querySelector('[data-formulario-auditoria]');

    if (!formulario) {
        return;
    }

    const selector = formulario.elements.cliente_id;
    const grupoNuevo = formulario.querySelector('[data-cliente-nuevo]');
    const camposNuevo = [formulario.elements.cliente_nombre, formulario.elements.cliente_sector];

    // Los datos del cliente nuevo solo se piden, y solo se ven, si se eligió crearlo.
    function actualizarClienteNuevo() {
        const esNuevo = selector.value === 'nuevo';

        grupoNuevo.hidden = !esNuevo;
        camposNuevo.forEach(function (campo) {
            campo.required = esNuevo;
        });
    }

    selector.addEventListener('change', actualizarClienteNuevo);
    actualizarClienteNuevo();

    // Un campo con solo espacios no cuenta como completo; el servidor también los recorta.
    camposNuevo.forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.setCustomValidity(campo.value !== '' && campo.value.trim() === '' ? 'Escribe un valor.' : '');
        });
    });

    // Evita registrar dos veces la auditoría con un doble clic.
    formulario.addEventListener('submit', function () {
        formulario.querySelector('[type="submit"]').disabled = true;
    });
})();
