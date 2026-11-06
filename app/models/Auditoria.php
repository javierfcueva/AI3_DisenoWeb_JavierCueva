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
                    a.cls, a.checks_aprobados, a.notas, a.fecha_creacion
             FROM auditorias AS a
             INNER JOIN clientes AS c ON c.id = a.cliente_id
             ORDER BY a.fecha_creacion DESC, a.id DESC'
        );
        $consulta->execute();

        return $consulta->fetchAll();
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

        $consulta->execute();

        return (int) $this->conexion->lastInsertId();
    }
}
