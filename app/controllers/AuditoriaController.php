<?php

declare(strict_types=1);

final class AuditoriaController
{
    private const ERROR_DUPLICADO = 1062;
    private const ERROR_CLIENTE_INEXISTENTE = 1452;

    public function listar(): void
    {
        renderizar('auditorias/listado', [
            'titulo' => 'Auditorías',
            'seccion' => 'listado',
            'auditorias' => (new Auditoria(obtenerConexion()))->listar(),
            'aviso' => tomarAviso(),
        ]);
    }

    public function nueva(): void
    {
        $this->mostrarFormulario(ValidadorAuditoria::valoresVacios(), [], 200);
    }

    public function guardar(): void
    {
        verificarCsrf();

        $resultado = (new ValidadorAuditoria())->validar($_POST);
        $valores = $resultado['valores'];
        $errores = $resultado['errores'];

        if ($errores !== []) {
            $this->mostrarFormulario($valores, $errores, 422);
            return;
        }

        try {
            $id = $this->registrar($valores);
        } catch (PDOException $error) {
            $this->mostrarFormulario($valores, $this->traducirError($error), 422);
            return;
        }

        guardarAviso("Auditoría número {$id} registrada correctamente.");
        redirigir('/auditorias');
    }

    /**
     * Guarda la auditoría y, si corresponde, su cliente nuevo: se guardan los dos o ninguno.
     *
     * @param array<string, string> $valores
     */
    private function registrar(array $valores): int
    {
        $conexion = obtenerConexion();
        $conexion->beginTransaction();

        try {
            $clienteId = $valores['cliente_id'] === 'nuevo'
                ? (new Cliente($conexion))->crear($valores['cliente_nombre'], $valores['cliente_sector'])
                : (int) $valores['cliente_id'];

            $id = (new Auditoria($conexion))->crear($clienteId, $valores);
            $conexion->commit();

            return $id;
        } catch (Throwable $error) {
            $conexion->rollBack();
            throw $error;
        }
    }

    /**
     * Convierte en un mensaje de formulario las reglas que rechaza la base de datos.
     * Cualquier otro error sigue su curso: se registra y el usuario ve la página genérica.
     *
     * @return array<string, string>
     */
    private function traducirError(PDOException $error): array
    {
        return match ($error->errorInfo[1] ?? null) {
            self::ERROR_DUPLICADO => ['cliente_nombre' => 'Ya existe un cliente con ese nombre. Selecciónalo en la lista.'],
            self::ERROR_CLIENTE_INEXISTENTE => ['cliente_id' => 'El cliente seleccionado no existe.'],
            default => throw $error,
        };
    }

    /**
     * @param array<string, string> $valores
     * @param array<string, string> $errores
     */
    private function mostrarFormulario(array $valores, array $errores, int $estado): void
    {
        $clientes = (new Cliente(obtenerConexion()))->listar();

        // Sin clientes registrados, la única opción posible es crear uno.
        if ($valores['cliente_id'] === '' && $clientes === []) {
            $valores['cliente_id'] = 'nuevo';
        }

        renderizar('auditorias/formulario', [
            'titulo' => 'Nueva auditoría',
            'seccion' => 'nueva',
            'clientes' => $clientes,
            'valores' => $valores,
            'errores' => $errores,
            'token' => tokenCsrf(),
        ], $estado);
    }
}
