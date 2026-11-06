<?php

declare(strict_types=1);

final class Cliente
{
    public function __construct(private PDO $conexion)
    {
    }

    /**
     * @return list<array{id: int, nombre: string}>
     */
    public function listar(): array
    {
        $consulta = $this->conexion->prepare('SELECT id, nombre FROM clientes ORDER BY nombre');
        $consulta->execute();

        return $consulta->fetchAll();
    }

    /**
     * Crea un cliente y devuelve su identificador.
     *
     * @throws PDOException con el código 1062 si el nombre ya existe.
     */
    public function crear(string $nombre, string $sector): int
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO clientes (nombre, sector) VALUES (:nombre, :sector)'
        );
        $consulta->bindValue(':nombre', $nombre);
        $consulta->bindValue(':sector', $sector);
        $consulta->execute();

        return (int) $this->conexion->lastInsertId();
    }
}
