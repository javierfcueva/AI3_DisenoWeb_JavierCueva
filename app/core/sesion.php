<?php

declare(strict_types=1);

/**
 * Inicia la sesión con una cookie inaccesible desde JavaScript y sin envío entre sitios.
 */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    session_name('ai3_sesion');
    session_set_cookie_params([
        'path' => '/',
        'secure' => ($_SERVER['HTTPS'] ?? 'off') !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Devuelve el token CSRF de la sesión y lo genera la primera vez.
 */
function tokenCsrf(): string
{
    iniciarSesion();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));

    return $_SESSION['csrf'];
}

/**
 * Detiene la petición si el token recibido no coincide con el de la sesión.
 */
function verificarCsrf(): void
{
    iniciarSesion();

    $recibido = $_POST['csrf'] ?? null;
    $esperado = $_SESSION['csrf'] ?? null;

    if (!is_string($recibido) || !is_string($esperado) || !hash_equals($esperado, $recibido)) {
        renderizarError(403);
        exit;
    }
}

/**
 * Guarda un mensaje para mostrarlo en la siguiente página.
 */
function guardarAviso(string $mensaje): void
{
    iniciarSesion();
    $_SESSION['aviso'] = $mensaje;
}

/**
 * Devuelve el mensaje pendiente, si lo hay, y lo elimina para que se muestre una sola vez.
 */
function tomarAviso(): ?string
{
    iniciarSesion();
    $mensaje = $_SESSION['aviso'] ?? null;
    unset($_SESSION['aviso']);

    return $mensaje;
}
