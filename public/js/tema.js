// Interruptor de tema claro y oscuro. Sin elección guardada, sigue al sistema operativo.
(function () {
    const CLAVE = 'tema';
    const raiz = document.documentElement;

    function leerGuardado() {
        try {
            return localStorage.getItem(CLAVE);
        } catch {
            return null; // El navegador puede tener bloqueado el almacenamiento
        }
    }

    function guardar(tema) {
        try {
            localStorage.setItem(CLAVE, tema);
        } catch {
            // El tema se aplica igual durante esta visita
        }
    }

    function temaActual() {
        if (raiz.dataset.tema) {
            return raiz.dataset.tema;
        }

        return matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro';
    }

    const guardado = leerGuardado();

    if (guardado === 'claro' || guardado === 'oscuro') {
        raiz.dataset.tema = guardado;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const boton = document.querySelector('[data-cambiar-tema]');

        function actualizarBoton() {
            boton.textContent = temaActual() === 'oscuro' ? 'Tema claro' : 'Tema oscuro';
        }

        boton.addEventListener('click', function () {
            const nuevo = temaActual() === 'oscuro' ? 'claro' : 'oscuro';
            raiz.dataset.tema = nuevo;
            guardar(nuevo);
            actualizarBoton();
        });

        // El botón solo aparece si JavaScript está disponible
        actualizarBoton();
        boton.hidden = false;
    });
})();
