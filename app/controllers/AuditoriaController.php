<?php

declare(strict_types=1);

final class AuditoriaController
{
    private const ERROR_DUPLICADO = 1062;
    private const ERROR_CLIENTE_INEXISTENTE = 1452;

    public function listar(): void
    {
        $modelo = new Auditoria(obtenerConexion());
        $auditorias = $modelo->listar();

        foreach ($auditorias as &$auditoria) {
            $auditoria['clasificacion'] = ClasificadorWebVitals::clasificar($auditoria);
        }
        unset($auditoria);

        renderizar('auditorias/listado', [
            'titulo' => 'Auditorías',
            'seccion' => 'listado',
            'auditorias' => $auditorias,
            'resumen' => ClasificadorWebVitals::resumir(array_column($auditorias, 'clasificacion')),
            'promedios' => $modelo->promedios(),
            'aviso' => tomarAviso(),
            'token' => tokenCsrf(),
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
            $id = $this->persistir($valores);
        } catch (PDOException $error) {
            $this->mostrarFormulario($valores, $this->traducirError($error), 422);
            return;
        }

        guardarAviso("Auditoría número {$id} registrada correctamente.");
        redirigir('/auditorias');
    }

    /**
     * Muestra el formulario con los datos guardados. El identificador llega en ?id=.
     */
    public function editar(): void
    {
        $id = ValidadorAuditoria::identificador($_GET['id'] ?? null);
        $auditoria = $id === null ? null : (new Auditoria(obtenerConexion()))->buscar($id);

        if ($auditoria === null) {
            renderizarError(404);
            return;
        }

        $this->mostrarFormulario($this->valoresDe($auditoria), [], 200, $id);
    }

    public function actualizar(): void
    {
        verificarCsrf();

        // Un identificador no válido o de una auditoría inexistente responde 404 antes de validar.
        $id = ValidadorAuditoria::identificador($_POST['id'] ?? null);

        if ($id === null || (new Auditoria(obtenerConexion()))->buscar($id) === null) {
            renderizarError(404);
            return;
        }

        $resultado = (new ValidadorAuditoria())->validar($_POST);
        $valores = $resultado['valores'];
        $errores = $resultado['errores'];

        if ($errores !== []) {
            $this->mostrarFormulario($valores, $errores, 422, $id);
            return;
        }

        try {
            $guardada = $this->persistir($valores, $id);
        } catch (PDOException $error) {
            $this->mostrarFormulario($valores, $this->traducirError($error), 422, $id);
            return;
        }

        // Otra petición la eliminó entre la comprobación y la actualización.
        if ($guardada === null) {
            renderizarError(404);
            return;
        }

        guardarAviso("Auditoría número {$id} actualizada correctamente.");
        redirigir('/auditorias');
    }

    public function eliminar(): void
    {
        verificarCsrf();

        $id = ValidadorAuditoria::identificador($_POST['id'] ?? null);

        if ($id === null || !(new Auditoria(obtenerConexion()))->eliminar($id)) {
            renderizarError(404);
            return;
        }

        guardarAviso("Auditoría número {$id} eliminada. Su cliente se conserva.");
        redirigir('/auditorias');
    }

    /**
     * Guarda la auditoría y, si corresponde, su cliente nuevo: se guardan los dos o ninguno.
     * Sin $id registra una auditoría; con $id la actualiza.
     *
     * @param array<string, string> $valores
     * @return int|null El identificador, o null si la auditoría a actualizar ya no existe.
     */
    private function persistir(array $valores, ?int $id = null): ?int
    {
        $conexion = obtenerConexion();
        $auditorias = new Auditoria($conexion);
        $conexion->beginTransaction();

        try {
            if ($id !== null && $auditorias->buscar($id, bloquear: true) === null) {
                $conexion->rollBack();
                return null;
            }

            $clienteId = $valores['cliente_id'] === 'nuevo'
                ? (new Cliente($conexion))->crear($valores['cliente_nombre'], $valores['cliente_sector'])
                : (int) $valores['cliente_id'];

            if ($id === null) {
                $id = $auditorias->crear($clienteId, $valores);
            } else {
                $auditorias->actualizar($id, $clienteId, $valores);
            }

            $conexion->commit();

            return $id;
        } catch (Throwable $error) {
            $conexion->rollBack();
            throw $error;
        }
    }

    /**
     * Convierte una fila de la base en los valores de texto que usa el formulario.
     *
     * @param array<string, mixed> $auditoria
     * @return array<string, string>
     */
    private function valoresDe(array $auditoria): array
    {
        $valores = ValidadorAuditoria::valoresVacios();

        foreach (['cliente_id', 'url', 'lcp_segundos', 'inp_ms', 'cls', 'checks_aprobados', 'notas'] as $campo) {
            $valores[$campo] = (string) $auditoria[$campo];
        }

        return $valores;
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
     * Muestra el formulario de alta o, si recibe $id, el de edición.
     *
     * @param array<string, string> $valores
     * @param array<string, string> $errores
     */
    private function mostrarFormulario(array $valores, array $errores, int $estado, ?int $id = null): void
    {
        $clientes = (new Cliente(obtenerConexion()))->listar();

        // Sin clientes registrados, la única opción posible es crear uno.
        if ($valores['cliente_id'] === '' && $clientes === []) {
            $valores['cliente_id'] = 'nuevo';
        }

        renderizar('auditorias/formulario', [
            'titulo' => $id === null ? 'Nueva auditoría' : 'Editar auditoría',
            'seccion' => $id === null ? 'nueva' : null,
            'id' => $id,
            'clientes' => $clientes,
            'valores' => $valores,
            'errores' => $errores,
            'token' => tokenCsrf(),
        ], $estado);
    }
}
