<?php

declare(strict_types=1);

/**
 * Escapa un dato dinámico antes de mostrarlo en HTML.
 */
function e(string|int|float|null $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Devuelve la carpeta del proyecto dentro del servidor, sin barra final.
 * Permite instalarlo en cualquier carpeta sin cambiar los enlaces.
 */
function rutaBase(): string
{
    return (string) preg_replace('#(/public)?/index\.php$#', '', $_SERVER['SCRIPT_NAME']);
}

/**
 * Construye un enlace interno a partir de una ruta como /css/estilos.css.
 */
function url(string $ruta = '/'): string
{
    return rutaBase() . '/' . ltrim($ruta, '/');
}

/**
 * Redirige con GET, de modo que recargar la página no repita el envío de un formulario.
 */
function redirigir(string $ruta): never
{
    header('Location: ' . url($ruta), true, 303);
    exit;
}

/**
 * Muestra una vista dentro de la plantilla general.
 *
 * @param array<string, mixed> $datos Variables disponibles en la vista.
 */
function renderizar(string $vista, array $datos = [], int $estado = 200): void
{
    http_response_code($estado);
    extract($datos, EXTR_SKIP);

    ob_start();
    require RAIZ . "/app/views/{$vista}.php";
    $contenido = ob_get_clean();

    require RAIZ . '/app/views/plantilla.php';
}

/**
 * Muestra la página de error de un código HTTP, sin datos del sistema.
 */
function renderizarError(int $estado): void
{
    $errores = [
        403 => ['Solicitud rechazada', 'La sesión expiró o el formulario no es válido. Vuelve a abrirlo e inténtalo de nuevo.'],
        404 => ['Página no encontrada', 'La dirección solicitada no existe.'],
        405 => ['Método no permitido', 'Esta dirección no admite ese tipo de petición.'],
        500 => ['Error inesperado', 'No se pudo completar la solicitud. Inténtalo de nuevo más tarde.'],
    ];

    [$titulo, $mensaje] = $errores[$estado];

    renderizar('error', ['titulo' => $titulo, 'mensaje' => $mensaje, 'codigo' => $estado], $estado);
}
