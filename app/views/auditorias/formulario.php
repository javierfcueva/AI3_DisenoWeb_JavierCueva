<?php

// Atributos y mensaje de error de un campo, para que el lector de pantalla los asocie.
$atributosError = static fn (string $campo): string => isset($errores[$campo])
    ? 'aria-invalid="true" aria-describedby="error-' . e($campo) . '"'
    : '';

$mensajeError = static fn (string $campo): string => isset($errores[$campo])
    ? '<p class="campo__error" id="error-' . e($campo) . '">' . e($errores[$campo]) . '</p>'
    : '';

$esClienteNuevo = $valores['cliente_id'] === 'nuevo';

?>
<section class="formulario-pagina">
    <h1>Nueva auditoría</h1>

    <?php if ($aviso !== null): ?>
        <p class="alerta alerta--exito" role="status"><?= e($aviso) ?></p>
    <?php endif; ?>

    <?php if ($errores !== []): ?>
        <p class="alerta alerta--error" role="alert">Revisa los campos marcados: la auditoría no se guardó.</p>
    <?php endif; ?>

    <form class="formulario" method="post" action="<?= e(url('/auditorias/nueva')) ?>" data-formulario-auditoria>
        <input type="hidden" name="csrf" value="<?= e($token) ?>">

        <fieldset class="grupo">
            <legend>Cliente</legend>

            <div class="campo">
                <label for="cliente_id">Cliente</label>
                <select id="cliente_id" name="cliente_id" required <?= $atributosError('cliente_id') ?>>
                    <option value="">Selecciona un cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?= e($cliente['id']) ?>" <?= $valores['cliente_id'] === (string) $cliente['id'] ? 'selected' : '' ?>>
                            <?= e($cliente['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="nuevo" <?= $esClienteNuevo ? 'selected' : '' ?>>Crear un cliente nuevo</option>
                </select>
                <?= $mensajeError('cliente_id') ?>
            </div>

            <div class="campos-dobles" data-cliente-nuevo>
                <div class="campo">
                    <label for="cliente_nombre">Nombre del cliente nuevo</label>
                    <input id="cliente_nombre" name="cliente_nombre" type="text" maxlength="120"
                           value="<?= e($valores['cliente_nombre']) ?>" <?= $atributosError('cliente_nombre') ?>>
                    <?= $mensajeError('cliente_nombre') ?>
                </div>

                <div class="campo">
                    <label for="cliente_sector">Sector del cliente nuevo</label>
                    <input id="cliente_sector" name="cliente_sector" type="text" maxlength="80"
                           value="<?= e($valores['cliente_sector']) ?>" <?= $atributosError('cliente_sector') ?>>
                    <?= $mensajeError('cliente_sector') ?>
                </div>
            </div>
        </fieldset>

        <fieldset class="grupo">
            <legend>Página auditada</legend>

            <div class="campo">
                <label for="url">URL</label>
                <input id="url" name="url" type="url" required maxlength="2048" pattern="[Hh][Tt][Tt][Pp][Ss]?://.+"
                       placeholder="https://ejemplo.com/landing"
                       value="<?= e($valores['url']) ?>" <?= $atributosError('url') ?>>
                <?= $mensajeError('url') ?>
            </div>
        </fieldset>

        <fieldset class="grupo">
            <legend>Resultados</legend>

            <div class="campos-dobles">
                <div class="campo">
                    <label for="lcp_segundos">LCP (segundos)</label>
                    <input id="lcp_segundos" name="lcp_segundos" type="number" required min="0" max="999.99" step="0.01"
                           value="<?= e($valores['lcp_segundos']) ?>" <?= $atributosError('lcp_segundos') ?>>
                    <?= $mensajeError('lcp_segundos') ?>
                </div>

                <div class="campo">
                    <label for="inp_ms">INP (milisegundos)</label>
                    <input id="inp_ms" name="inp_ms" type="number" required min="0" max="65535" step="1"
                           value="<?= e($valores['inp_ms']) ?>" <?= $atributosError('inp_ms') ?>>
                    <?= $mensajeError('inp_ms') ?>
                </div>

                <div class="campo">
                    <label for="cls">CLS</label>
                    <input id="cls" name="cls" type="number" required min="0" max="99.999" step="0.001"
                           value="<?= e($valores['cls']) ?>" <?= $atributosError('cls') ?>>
                    <?= $mensajeError('cls') ?>
                </div>

                <div class="campo">
                    <label for="checks_aprobados">Puntos aprobados del checklist (0 a 17)</label>
                    <input id="checks_aprobados" name="checks_aprobados" type="number" required min="0" max="17" step="1"
                           value="<?= e($valores['checks_aprobados']) ?>" <?= $atributosError('checks_aprobados') ?>>
                    <?= $mensajeError('checks_aprobados') ?>
                </div>
            </div>

            <div class="campo">
                <label for="notas">Notas (opcional)</label>
                <textarea id="notas" name="notas" rows="4" <?= $atributosError('notas') ?>><?= e($valores['notas']) ?></textarea>
                <?= $mensajeError('notas') ?>
            </div>
        </fieldset>

        <button class="boton boton--primario" type="submit">Guardar auditoría</button>
    </form>
</section>

<script src="<?= e(url('/js/formulario.js')) ?>" defer></script>
