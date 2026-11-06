<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Registro de auditorías de rendimiento y QA de landing pages.">
    <title><?= e($titulo) ?> · Registro de auditorías</title>
    <link rel="icon" href="<?= e(url('/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(url('/css/estilos.css')) ?>">
    <!-- Sin defer: aplica el tema guardado antes de pintar la página -->
    <script src="<?= e(url('/js/tema.js')) ?>"></script>
</head>
<body>
    <a class="saltar" href="#contenido">Saltar al contenido</a>

    <header class="encabezado">
        <div class="contenedor encabezado__fila">
            <a class="marca" href="<?= e(url('/')) ?>">Registro de auditorías</a>
            <nav class="navegacion" aria-label="Principal">
                <a href="<?= e(url('/auditorias')) ?>" <?= ($seccion ?? '') === 'listado' ? 'aria-current="page"' : '' ?>>Auditorías</a>
                <a href="<?= e(url('/auditorias/nueva')) ?>" <?= ($seccion ?? '') === 'nueva' ? 'aria-current="page"' : '' ?>>Nueva auditoría</a>
                <button class="boton" type="button" data-cambiar-tema hidden>Cambiar tema</button>
            </nav>
        </div>
    </header>

    <main id="contenido" class="contenedor principal">
        <?= $contenido ?>
    </main>

    <footer class="pie">
        <div class="contenedor">Actividad Integradora 3 · Programación de Sistemas Web</div>
    </footer>
</body>
</html>
