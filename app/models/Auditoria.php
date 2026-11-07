<?php

declare(strict_types=1);

final class Auditoria
{
    public function __construct(private PDO $conexion)
    {
    }

    /**
     * Devuelve las auditorías con su cliente, de la más reciente a la más antigua.
     *
     * @return list<array<string, mixed>>
     */
    public function listar(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT a.id, c.nombre AS cliente, c.sector, a.url, a.lcp_segundos, a.inp_ms,
                    a.cls, a.checks_aprobados, a.notas, a.fecha_creacion, a.fecha_actualizacion
             FROM auditorias AS a
             INNER JOIN clientes AS c ON c.id = a.cliente_id
             ORDER BY a.fecha_creacion DESC, a.id DESC'
        );
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Calcula en la base de datos los promedios de las métricas y del checklist.
     *
     * @return array{lcp_segundos: ?string, inp_ms: ?string, cls: ?string, checks_aprobados: ?string}
     */
    public function promedios(): array
    {
        $consulta = $this->conexion->prepare(
            'SELECT AVG(lcp_segundos) AS lcp_segundos, AVG(inp_ms) AS inp_ms,
                    AVG(cls) AS cls, AVG(checks_aprobados) AS checks_aprobados
             FROM auditorias'
        );
        $consulta->execute();

        return $consulta->fetch();
    }

    /**
     * Devuelve una auditoría o null si no existe.
     *
     * @param bool $bloquear Dentro de una transacción, reserva la fila hasta el commit para que
     *                       otra petición no la elimine mientras se actualiza.
     * @return array<string, mixed>|null
     */
    public function buscar(int $id, bool $bloquear = false): ?array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id, cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas
             FROM auditorias
             WHERE id = :id'
            . ($bloquear ? ' FOR UPDATE' : '')
        );
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        $auditoria = $consulta->fetch();

        return $auditoria === false ? null : $auditoria;
    }

    /**
     * Registra una auditoría y devuelve su identificador.
     *
     * @param array<string, string> $datos Valores ya validados.
     * @throws PDOException con el código 1452 si el cliente no existe.
     */
    public function crear(int $clienteId, array $datos): int
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO auditorias
                (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
             VALUES
                (:cliente_id, :url, :lcp_segundos, :inp_ms, :cls, :checks_aprobados, :notas)'
        );

        $this->vincularDatos($consulta, $clienteId, $datos);
        $consulta->execute();

        return (int) $this->conexion->lastInsertId();
    }

    /**
     * Reemplaza los datos de una auditoría existente. La base actualiza fecha_actualizacion
     * por sí sola y conserva fecha_creacion.
     *
     * No se usa rowCount() para saber si la auditoría existe: MariaDB devuelve 0 también cuando
     * los valores enviados son iguales a los guardados. La existencia se comprueba antes con buscar().
     *
     * @param array<string, string> $datos Valores ya validados.
     * @throws PDOException con el código 1452 si el cliente no existe.
     */
    public function actualizar(int $id, int $clienteId, array $datos): void
    {
        $consulta = $this->conexion->prepare(
            'UPDATE auditorias
             SET cliente_id = :cliente_id, url = :url, lcp_segundos = :lcp_segundos, inp_ms = :inp_ms,
                 cls = :cls, checks_aprobados = :checks_aprobados, notas = :notas
             WHERE id = :id'
        );

        $this->vincularDatos($consulta, $clienteId, $datos);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    /**
     * Elimina una auditoría; su cliente se conserva. Devuelve false si no existía.
     */
    public function eliminar(int $id): bool
    {
        $consulta = $this->conexion->prepare('DELETE FROM auditorias WHERE id = :id');
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        // En un DELETE, rowCount() sí indica si la fila existía.
        return $consulta->rowCount() === 1;
    }

    /**
     * Asocia a la consulta los valores comunes al alta y a la edición.
     *
     * @param array<string, string> $datos
     */
    private function vincularDatos(PDOStatement $consulta, int $clienteId, array $datos): void
    {
        $consulta->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
        $consulta->bindValue(':url', $datos['url']);
        $consulta->bindValue(':lcp_segundos', $datos['lcp_segundos']);
        $consulta->bindValue(':inp_ms', (int) $datos['inp_ms'], PDO::PARAM_INT);
        $consulta->bindValue(':cls', $datos['cls']);
        $consulta->bindValue(':checks_aprobados', (int) $datos['checks_aprobados'], PDO::PARAM_INT);

        if ($datos['notas'] === '') {
            $consulta->bindValue(':notas', null, PDO::PARAM_NULL);
        } else {
            $consulta->bindValue(':notas', $datos['notas']);
        }
    }
}
