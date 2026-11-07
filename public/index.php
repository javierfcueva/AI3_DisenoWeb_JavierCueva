<?php

declare(strict_types=1);

/**
 * Controlador frontal: toda petición que no sea un archivo público entra por aquí.
 */

// XAMPP trae display_errors activado; aquí se desactiva sin depender de php.ini.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

define('RAIZ', dirname(__DIR__));

require RAIZ . '/config/conexion.php';
require RAIZ . '/app/core/helpers.php';
require RAIZ . '/app/core/sesion.php';
require RAIZ . '/app/core/Enrutador.php';

spl_autoload_register(static function (string $clase): void {
    foreach (['controllers', 'models'] as $carpeta) {
        $archivo = RAIZ . "/app/{$carpeta}/{$clase}.php";

        if (is_file($archivo)) {
            require $archivo;
            return;
        }
    }
});

// Cualquier error sin capturar queda en el log; el usuario solo ve una página genérica.
set_exception_handler(static function (Throwable $error): void {
    error_log(sprintf(
        'Error no controlado: %s en %s:%d',
        $error->getMessage(),
        $error->getFile(),
        $error->getLine()
    ));

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    try {
        renderizarError(500);
    } catch (Throwable) {
        http_response_code(500);
        echo 'Se produjo un error inesperado.';
    }
});

header_remove('X-Powered-By');
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");

$enrutador = new Enrutador();
$enrutador->get('/', [InicioController::class, 'index']);
$enrutador->get('/auditorias', [AuditoriaController::class, 'listar']);
$enrutador->get('/auditorias/nueva', [AuditoriaController::class, 'nueva']);
$enrutador->post('/auditorias/nueva', [AuditoriaController::class, 'guardar']);
$enrutador->get('/auditorias/editar', [AuditoriaController::class, 'editar']);
$enrutador->post('/auditorias/editar', [AuditoriaController::class, 'actualizar']);
$enrutador->post('/auditorias/eliminar', [AuditoriaController::class, 'eliminar']);

$enrutador->despachar($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
