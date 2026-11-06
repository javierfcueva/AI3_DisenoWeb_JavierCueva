<?php

declare(strict_types=1);

/**
 * Asocia cada método HTTP y ruta con la acción de un controlador.
 */
final class Enrutador
{
    /** @var array<string, array<string, array{class-string, string}>> */
    private array $rutas = [];

    /**
     * @param array{class-string, string} $accion
     */
    public function get(string $ruta, array $accion): void
    {
        $this->rutas[$ruta]['GET'] = $accion;
    }

    /**
     * @param array{class-string, string} $accion
     */
    public function post(string $ruta, array $accion): void
    {
        $this->rutas[$ruta]['POST'] = $accion;
    }

    public function despachar(string $metodo, string $uri): void
    {
        $metodos = $this->rutas[$this->obtenerRuta($uri)] ?? null;

        if ($metodos === null) {
            renderizarError(404);
            return;
        }

        // HEAD responde igual que GET; el servidor omite el cuerpo de la respuesta.
        $accion = $metodos[$metodo === 'HEAD' ? 'GET' : $metodo] ?? null;

        if ($accion === null) {
            $permitidos = array_keys($metodos);

            if (isset($metodos['GET'])) {
                $permitidos[] = 'HEAD';
            }

            header('Allow: ' . implode(', ', $permitidos));
            renderizarError(405);
            return;
        }

        [$controlador, $nombreAccion] = $accion;
        (new $controlador())->$nombreAccion();
    }

    /**
     * Quita la cadena de consulta y la carpeta del proyecto: /proyecto/inicio/?a=1 es /inicio.
     */
    private function obtenerRuta(string $uri): string
    {
        $ruta = rawurldecode(explode('?', $uri, 2)[0]);
        $base = rutaBase();

        // Sin distinguir mayúsculas: en Windows la carpeta responde con cualquier combinación.
        if ($base !== '' && stripos($ruta, $base) === 0) {
            $ruta = substr($ruta, strlen($base));
        }

        return '/' . trim($ruta, '/');
    }
}
