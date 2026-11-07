-- Datos de prueba ficticios para el registro de auditorías
-- Ejecutar después de db_sistema_web.sql y una sola vez, con las tablas vacías.
--
-- Los clientes son inventados y las direcciones usan el dominio reservado .example
-- (RFC 2606), que no pertenece a ninguna empresa real. Las métricas cubren las tres
-- clasificaciones: Aprobada, Mejorable y Crítica.
--
-- Todo se carga en una transacción: si una sentencia falla, no se guarda nada.
-- Una segunda ejecución se detiene en el primer cliente por el nombre duplicado
-- (uq_clientes_nombre), así que nunca repite registros.

SET NAMES utf8mb4;

USE db_sistema_web;

START TRANSACTION;

INSERT INTO clientes (nombre, sector) VALUES
    ('Clínica Dental Sonríe', 'Salud'),
    ('Ferretería El Tornillo', 'Comercio minorista'),
    ('Academia de Idiomas Políglota', 'Educación'),
    ('Inmobiliaria Horizonte Costero', 'Bienes raíces'),
    ('Café Tostado del Valle', 'Gastronomía'),
    ('Estudio Contable Balance', 'Servicios profesionales');

-- Cada auditoría busca a su cliente por nombre, sin depender de los identificadores.
-- Columnas: cliente, URL, LCP (s), INP (ms), CLS, puntos del checklist (0 a 17), notas.
INSERT INTO auditorias (cliente_id, url, lcp_segundos, inp_ms, cls, checks_aprobados, notas) VALUES
    ((SELECT id FROM clientes WHERE nombre = 'Ferretería El Tornillo'),
        'https://www.ferreteria-eltornillo.example/',
        4.80, 380, 0.310, 9,
        'Imagen principal de 2,4 MB sin comprimir y carrusel sin espacio reservado que empuja el contenido al cargar. Faltan las etiquetas Open Graph y el formulario de contacto no muestra los errores.'),

    ((SELECT id FROM clientes WHERE nombre = 'Clínica Dental Sonríe'),
        'https://www.clinica-sonrie.example/ortodoncia',
        2.10, 140, 0.040, 16,
        'Falta el texto alternativo en dos fotos del equipo.'),

    ((SELECT id FROM clientes WHERE nombre = 'Academia de Idiomas Políglota'),
        'https://academia-poliglota.example/cursos/ingles-intensivo',
        3.20, 260, 0.080, 13,
        'Las fuentes web se cargan sin font-display: swap y el widget de chat retrasa la primera interacción.'),

    ((SELECT id FROM clientes WHERE nombre = 'Inmobiliaria Horizonte Costero'),
        'https://www.horizonte-costero.example/proyectos/torre-mirador',
        5.60, 620, 0.180, 8,
        'La galería carga sus 40 fotos al inicio y el mapa incrustado no usa carga diferida. En móviles, el botón flotante de mensajería tapa el formulario.'),

    ((SELECT id FROM clientes WHERE nombre = 'Café Tostado del Valle'),
        'https://cafe-tostado.example/menu',
        1.90, 120, 0.120, 15,
        'El aviso de cookies desplaza el menú hacia abajo al aparecer.'),

    ((SELECT id FROM clientes WHERE nombre = 'Estudio Contable Balance'),
        'https://www.balance-contable.example/servicios/declaraciones',
        2.40, 180, 0.020, 17,
        NULL),

    ((SELECT id FROM clientes WHERE nombre = 'Clínica Dental Sonríe'),
        'https://www.clinica-sonrie.example/agenda-tu-cita',
        2.90, 210, 0.060, 14,
        'El calendario de citas depende de un script externo pesado que retrasa la carga y la respuesta al elegir una fecha.'),

    ((SELECT id FROM clientes WHERE nombre = 'Academia de Idiomas Políglota'),
        'https://academia-poliglota.example/becas',
        2.30, 160, 0.090, 12,
        'Las métricas son buenas, pero el formulario no enlaza la política de privacidad y dos campos no tienen etiqueta.'),

    ((SELECT id FROM clientes WHERE nombre = 'Café Tostado del Valle'),
        'https://cafe-tostado.example/pedidos',
        3.80, 540, 0.050, 11,
        'El cálculo del carrito bloquea el hilo principal cada vez que se cambia una cantidad.'),

    ((SELECT id FROM clientes WHERE nombre = 'Estudio Contable Balance'),
        'https://www.balance-contable.example/contacto',
        1.70, 90, 0.000, 17,
        NULL),

    ((SELECT id FROM clientes WHERE nombre = 'Inmobiliaria Horizonte Costero'),
        'https://www.horizonte-costero.example/proyectos/torre-mirador',
        3.10, 230, 0.070, 14,
        'Nueva medición tras activar la carga diferida de la galería y del mapa. Mejoraron las tres métricas; falta aligerar el script de analítica.'),

    ((SELECT id FROM clientes WHERE nombre = 'Ferretería El Tornillo'),
        'https://www.ferreteria-eltornillo.example/',
        2.20, 150, 0.030, 16,
        'Nueva medición tras convertir las imágenes a WebP y reservar el espacio del carrusel. Falta agregar las etiquetas Open Graph.');

COMMIT;
