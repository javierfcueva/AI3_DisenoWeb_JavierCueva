<?php

declare(strict_types=1);

/**
 * Conexión a la base de datos con PDO.
 *
 * Las credenciales se leen del archivo .env, que no se versiona. Si la conexión
 * falla, el detalle queda en el log de PHP y el usuario solo ve un mensaje genérico.
 */

// XAMPP trae display_errors activado; aquí se desactiva sin depender de php.ini.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// El modo global de XAMPP no es estricto: MariaDB recortaría los valores fuera de
// rango sin dar error. Se fija por sesión para no alterar la configuración global.
const SQL_MODE_SESION = 'STRICT_ALL_TABLES,ERROR_FOR_DIVISION_BY_ZERO,'
    . 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION';

const VARIABLES_REQUERIDAS = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];

/**
 * Lee un archivo con líneas CLAVE=valor. Ignora las líneas vacías y los comentarios.
 *
 * @return array<string, string>
 * @throws RuntimeException si el archivo no se puede leer.
 */
function leerEntorno(string $ruta): array
{
    $lineas = is_readable($ruta) ? file($ruta, FILE_IGNORE_NEW_LINES) : false;

    if ($lineas === false) {
        throw new RuntimeException('No se pudo leer el archivo .env.');
    }

    $entorno = [];

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $valor = trim($valor);

        // Las comillas permiten escribir valores con espacios al inicio o al final.
        if (preg_match('/^(["\'])(.*)\1$/', $valor, $partes) === 1) {
            $valor = $partes[2];
        }

        $entorno[trim($clave)] = $valor;
    }

    return $entorno;
}

/**
 * Crea la conexión a partir de la configuración de .env.
 *
 * @param array<string, string> $entorno
 * @throws RuntimeException si falta una variable.
 * @throws PDOException si la base de datos rechaza la conexión.
 */
function crearConexion(array $entorno): PDO
{
    foreach (VARIABLES_REQUERIDAS as $variable) {
        if (!array_key_exists($variable, $entorno)) {
            throw new RuntimeException("Falta la variable {$variable} en el archivo .env.");
        }
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $entorno['DB_HOST'],
        $entorno['DB_PORT'],
        $entorno['DB_NAME']
    );

    $conexion = new PDO($dsn, $entorno['DB_USER'], $entorno['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $conexion->exec("SET SESSION sql_mode = '" . SQL_MODE_SESION . "'");

    return $conexion;
}

/**
 * Devuelve la conexión de la petición actual y la crea la primera vez que se necesita.
 * Si no es posible conectarse, registra el motivo y termina con un mensaje genérico.
 */
function obtenerConexion(): PDO
{
    static $conexion = null;

    if ($conexion instanceof PDO) {
        return $conexion;
    }

    try {
        $conexion = crearConexion(leerEntorno(dirname(__DIR__) . '/.env'));
    } catch (PDOException | RuntimeException $error) {
        // Solo el código y el mensaje: la traza podría incluir datos de la conexión.
        error_log(sprintf('Error de conexión [%s]: %s', $error->getCode(), $error->getMessage()));
        terminarConErrorGenerico();
    }

    return $conexion;
}

/**
 * Responde al usuario sin revelar credenciales ni datos del sistema.
 */
function terminarConErrorGenerico(): never
{
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/plain; charset=UTF-8');
    }

    echo 'El servicio no está disponible en este momento. Inténtalo de nuevo más tarde.';
    exit(1);
}
