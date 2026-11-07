-- Datos de prueba ficticios para el registro de auditorías
-- Ejecutar después de db_sistema_web.sql.
--
-- Los clientes son inventados y las direcciones usan el dominio reservado .example
-- (RFC 2606), que no pertenece a ninguna empresa real. Las métricas cubren las tres
-- clasificaciones: Aprobada, Mejorable y Crítica.
--
-- Solo se cargan si las dos tablas están vacías. Si ya tienen registros, por ejemplo
-- al ejecutar el script por segunda vez, no se agrega nada y nunca se repiten datos.
-- La condición está en cada INSERT, así que no depende de que el programa que ejecuta
-- el script se detenga ante un error. Cada fila va en su propio INSERT para que los
-- identificadores queden consecutivos, sin huecos.

SET NAMES utf8mb4;

USE db_sistema_web;

START TRANSACTION;

SET @cargar_datos = NOT EXISTS (SELECT 1 FROM clientes)
                AND NOT EXISTS (SELECT 1 FROM auditorias);

-- Clientes
INSERT INTO clientes (nombre, sector)
    SELECT 'Clínica Dental Sonríe', 'Salud' FROM DUAL WHERE @cargar_datos;
INSERT INTO clientes (nombre, sector)
    SELECT 'Ferretería El Tornillo', 'Comercio minorista' FROM DUAL WHERE @cargar_datos;
INSERT INTO clientes (nombre, sector)
    SELECT 'Academia de Idiomas Políglota', 'Educación' FROM DUAL WHERE @cargar_datos;
INSERT INTO clientes (nombre, sector)
    SELECT 'Inmobiliaria Horizonte Costero', 'Bienes raíces' FROM DUAL WHERE @cargar_datos;
INSERT INTO clientes (nombre, sector)
    SELECT 'Café Tostado del Valle', 'Gastronomía' FROM DUAL WHERE @cargar_datos;
INSERT INTO clientes (nombre, sector)
    SELECT 'Estudio Contable Balance', 'Servicios profesionales' FROM DUAL WHERE @cargar_datos;

-- Auditorías: cada una busca a su cliente por nombre, sin depender de los identificadores.
-- Valores: URL, LCP (s), INP (ms), CLS, puntos del checklist (0 a 17) y notas.
INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.ferreteria-eltornillo.example/', 4.80, 380, 0.310, 9,
        'Imagen principal de 2,4 MB sin comprimir y carrusel sin espacio reservado que empuja el contenido al cargar. Faltan las etiquetas Open Graph y el formulario de contacto no muestra los errores.'
    FROM clientes WHERE nombre = 'Ferretería El Tornillo' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.clinica-sonrie.example/ortodoncia', 2.10, 140, 0.040, 16,
        'Falta el texto alternativo en dos fotos del equipo.'
    FROM clientes WHERE nombre = 'Clínica Dental Sonríe' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://academia-poliglota.example/cursos/ingles-intensivo', 3.20, 260, 0.080, 13,
        'Las fuentes web se cargan sin font-display: swap y el widget de chat retrasa la primera interacción.'
    FROM clientes WHERE nombre = 'Academia de Idiomas Políglota' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.horizonte-costero.example/proyectos/torre-mirador', 5.60, 620, 0.180, 8,
        'La galería carga sus 40 fotos al inicio y el mapa incrustado no usa carga diferida. En móviles, el botón flotante de mensajería tapa el formulario.'
    FROM clientes WHERE nombre = 'Inmobiliaria Horizonte Costero' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://cafe-tostado.example/menu', 1.90, 120, 0.120, 15,
        'El aviso de cookies desplaza el menú hacia abajo al aparecer.'
    FROM clientes WHERE nombre = 'Café Tostado del Valle' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.balance-contable.example/servicios/declaraciones', 2.40, 180, 0.020, 17,
        NULL
    FROM clientes WHERE nombre = 'Estudio Contable Balance' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.clinica-sonrie.example/agenda-tu-cita', 2.90, 210, 0.060, 14,
        'El calendario de citas depende de un script externo pesado que retrasa la carga y la respuesta al elegir una fecha.'
    FROM clientes WHERE nombre = 'Clínica Dental Sonríe' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://academia-poliglota.example/becas', 2.30, 160, 0.090, 12,
        'Las métricas son buenas, pero el formulario no enlaza la política de privacidad y dos campos no tienen etiqueta.'
    FROM clientes WHERE nombre = 'Academia de Idiomas Políglota' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://cafe-tostado.example/pedidos', 3.80, 540, 0.050, 11,
        'El cálculo del carrito bloquea el hilo principal cada vez que se cambia una cantidad.'
    FROM clientes WHERE nombre = 'Café Tostado del Valle' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.balance-contable.example/contacto', 1.70, 90, 0.000, 17,
        NULL
    FROM clientes WHERE nombre = 'Estudio Contable Balance' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.horizonte-costero.example/proyectos/torre-mirador', 3.10, 230, 0.070, 14,
        'Nueva medición tras activar la carga diferida de la galería y del mapa. Mejoraron las tres métricas; falta aligerar el script de analítica.'
    FROM clientes WHERE nombre = 'Inmobiliaria Horizonte Costero' AND @cargar_datos;

INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas)
    SELECT id, 'https://www.ferreteria-eltornillo.example/', 2.20, 150, 0.030, 16,
        'Nueva medición tras convertir las imágenes a WebP y reservar el espacio del carrusel. Falta agregar las etiquetas Open Graph.'
    FROM clientes WHERE nombre = 'Ferretería El Tornillo' AND @cargar_datos;

COMMIT;
