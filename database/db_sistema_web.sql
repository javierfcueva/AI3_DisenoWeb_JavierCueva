-- Registro de auditorías de rendimiento y QA de landing pages
-- Script DDL para MariaDB 10.11 / MySQL 8

CREATE DATABASE IF NOT EXISTS db_sistema_web
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_sistema_web;

-- Clientes a los que pertenecen las páginas auditadas
CREATE TABLE IF NOT EXISTS clientes (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre         VARCHAR(120) NOT NULL,
    sector         VARCHAR(80)  NOT NULL,
    fecha_creacion TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla principal: una fila por cada auditoría de una página
CREATE TABLE IF NOT EXISTS auditorias (
    id                  INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    cliente_id          INT UNSIGNED      NOT NULL,
    url                 VARCHAR(2048)     NOT NULL,
    lcp_segundos        DECIMAL(5,2)      NOT NULL, -- Velocidad de carga
    inp_ms              SMALLINT UNSIGNED NOT NULL, -- Respuesta a la interacción
    cls                 DECIMAL(5,3)      NOT NULL, -- Estabilidad visual
    checks_aprobados    TINYINT UNSIGNED  NOT NULL, -- Puntos cumplidos del checklist
    notas               TEXT              NULL,
    fecha_creacion      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditorias_cliente (cliente_id),
    -- Un cliente con auditorías registradas no se puede eliminar
    CONSTRAINT fk_auditorias_cliente
        FOREIGN KEY (cliente_id) REFERENCES clientes (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT chk_auditorias_lcp    CHECK (lcp_segundos >= 0),
    CONSTRAINT chk_auditorias_cls    CHECK (cls >= 0),
    CONSTRAINT chk_auditorias_checks CHECK (checks_aprobados <= 17)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
