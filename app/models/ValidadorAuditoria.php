<?php

declare(strict_types=1);

/**
 * Validación en el servidor de los datos de una auditoría. No depende del navegador:
 * también se aplica a las peticiones que no pasan por el formulario.
 */
final class ValidadorAuditoria
{
    private const CAMPOS = [
        'cliente_id', 'cliente_nombre', 'cliente_sector', 'url',
        'lcp_segundos', 'inp_ms', 'cls', 'checks_aprobados', 'notas',
    ];

    private const ID_MAXIMO = 4294967295;
    private const URL_MAXIMA = 2048;
    private const NOMBRE_MAXIMO = 120;
    private const SECTOR_MAXIMO = 80;
    private const NOTAS_MAXIMO_BYTES = 65535;

    /** @var array<string, string> */
    private array $errores = [];

    /**
     * Valores iniciales de un formulario vacío.
     *
     * @return array<string, string>
     */
    public static function valoresVacios(): array
    {
        return array_fill_keys(self::CAMPOS, '');
    }

    /**
     * Convierte en número un identificador recibido en la URL o en un formulario.
     * Devuelve null si no es un entero positivo dentro del rango de INT UNSIGNED.
     */
    public static function identificador(mixed $valor): ?int
    {
        if (!is_string($valor) || preg_match('/^[1-9]\d{0,9}$/', $valor) !== 1 || (int) $valor > self::ID_MAXIMO) {
            return null;
        }

        return (int) $valor;
    }

    /**
     * @param array<string, mixed> $entrada
     * @return array{valores: array<string, string>, errores: array<string, string>}
     */
    public function validar(array $entrada): array
    {
        $this->errores = [];
        $valores = [];

        foreach (self::CAMPOS as $campo) {
            $valores[$campo] = $this->leerTexto($entrada[$campo] ?? '', $campo);
        }

        $this->validarCliente($valores);
        $this->validarUrl($valores['url']);

        $this->validarNumero(
            'lcp_segundos',
            $valores['lcp_segundos'],
            '/^\d{1,3}(\.\d{1,2})?$/',
            999.99,
            'El LCP debe estar entre 0 y 999.99 segundos, con dos decimales como máximo.'
        );
        $this->validarNumero(
            'inp_ms',
            $valores['inp_ms'],
            '/^\d{1,5}$/',
            65535,
            'El INP debe ser un número entero entre 0 y 65535 milisegundos.'
        );
        $this->validarNumero(
            'cls',
            $valores['cls'],
            '/^\d{1,2}(\.\d{1,3})?$/',
            99.999,
            'El CLS debe estar entre 0 y 99.999, con tres decimales como máximo.'
        );
        $this->validarNumero(
            'checks_aprobados',
            $valores['checks_aprobados'],
            '/^\d{1,2}$/',
            17,
            'Los puntos aprobados deben ser un número entero entre 0 y 17.'
        );

        if (strlen($valores['notas']) > self::NOTAS_MAXIMO_BYTES) {
            $this->agregarError('notas', 'Las notas son demasiado largas.');
        }

        return ['valores' => $valores, 'errores' => $this->errores];
    }

    /**
     * Un campo enviado como lista (campo[]=x) o con bytes no válidos no se acepta como texto.
     */
    private function leerTexto(mixed $valor, string $campo): string
    {
        $esTextoValido = is_string($valor)
            && mb_check_encoding($valor, 'UTF-8')
            && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $valor) !== 1;

        if (!$esTextoValido) {
            $this->agregarError($campo, 'El valor contiene caracteres no válidos.');
            return '';
        }

        return trim($valor);
    }

    /**
     * @param array<string, string> $valores
     */
    private function validarCliente(array $valores): void
    {
        $id = $valores['cliente_id'];

        if ($id === 'nuevo') {
            $this->validarTexto('cliente_nombre', $valores['cliente_nombre'], self::NOMBRE_MAXIMO, 'El nombre del cliente');
            $this->validarTexto('cliente_sector', $valores['cliente_sector'], self::SECTOR_MAXIMO, 'El sector');
            return;
        }

        if (self::identificador($id) === null) {
            $this->agregarError('cliente_id', 'Selecciona un cliente de la lista o crea uno nuevo.');
        }
    }

    private function validarTexto(string $campo, string $valor, int $maximo, string $etiqueta): void
    {
        if ($valor === '') {
            $this->agregarError($campo, "{$etiqueta} es obligatorio.");
        } elseif (mb_strlen($valor, 'UTF-8') > $maximo) {
            $this->agregarError($campo, "{$etiqueta} admite {$maximo} caracteres como máximo.");
        }
    }

    private function validarUrl(string $url): void
    {
        $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($url === '') {
            $this->agregarError('url', 'La URL es obligatoria.');
        } elseif (strlen($url) > self::URL_MAXIMA) {
            $this->agregarError('url', 'La URL admite ' . self::URL_MAXIMA . ' caracteres como máximo.');
        } elseif (filter_var($url, FILTER_VALIDATE_URL) === false || !in_array($esquema, ['http', 'https'], true)) {
            $this->agregarError('url', 'Escribe una URL completa que empiece por http:// o https://.');
        }
    }

    private function validarNumero(string $campo, string $valor, string $formato, float $maximo, string $mensaje): void
    {
        if (preg_match($formato, $valor) !== 1 || (float) $valor > $maximo) {
            $this->agregarError($campo, $mensaje);
        }
    }

    /**
     * Conserva el primer error de cada campo.
     */
    private function agregarError(string $campo, string $mensaje): void
    {
        $this->errores[$campo] ??= $mensaje;
    }
}
