<section>
    <div class="cabecera-pagina">
        <h1>Auditorías</h1>
        <a class="boton boton--primario" href="<?= e(url('/auditorias/nueva')) ?>">Nueva auditoría</a>
    </div>

    <?php if ($aviso !== null): ?>
        <p class="alerta alerta--exito" role="status"><?= e($aviso) ?></p>
    <?php endif; ?>

    <?php if ($auditorias === []): ?>
        <p class="vacio">Aún no hay auditorías registradas.</p>
    <?php else: ?>
        <div class="tabla-contenedor">
            <table class="tabla">
                <caption class="solo-lectores">Auditorías registradas, de la más reciente a la más antigua</caption>
                <thead>
                    <tr>
                        <th scope="col">N.º</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Página</th>
                        <th scope="col" class="tabla__numero">LCP</th>
                        <th scope="col" class="tabla__numero">INP</th>
                        <th scope="col" class="tabla__numero">CLS</th>
                        <th scope="col" class="tabla__numero">Checklist</th>
                        <th scope="col">Notas</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditorias as $auditoria): ?>
                        <tr>
                            <th scope="row" data-etiqueta="N.º"><?= e($auditoria['id']) ?></th>
                            <td data-etiqueta="Cliente">
                                <div>
                                    <?= e($auditoria['cliente']) ?>
                                    <span class="tabla__secundario"><?= e($auditoria['sector']) ?></span>
                                </div>
                            </td>
                            <td data-etiqueta="Página">
                                <?php if (esEnlaceWeb($auditoria['url'])): ?>
                                    <a class="tabla__url" href="<?= e($auditoria['url']) ?>"><?= e($auditoria['url']) ?></a>
                                <?php else: ?>
                                    <span class="tabla__url"><?= e($auditoria['url']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="tabla__numero" data-etiqueta="LCP"><?= e(numero($auditoria['lcp_segundos'], 2)) ?> s</td>
                            <td class="tabla__numero" data-etiqueta="INP"><?= e(numero($auditoria['inp_ms'])) ?> ms</td>
                            <td class="tabla__numero" data-etiqueta="CLS"><?= e(numero($auditoria['cls'], 3)) ?></td>
                            <td class="tabla__numero" data-etiqueta="Checklist"><?= e($auditoria['checks_aprobados']) ?> de 17</td>
                            <td data-etiqueta="Notas">
                                <?php if ($auditoria['notas'] === null): ?>
                                    <span class="tabla__secundario">Sin notas</span>
                                <?php else: ?>
                                    <details class="notas">
                                        <summary>Ver notas</summary>
                                        <p><?= e($auditoria['notas']) ?></p>
                                    </details>
                                <?php endif; ?>
                            </td>
                            <td data-etiqueta="Fecha">
                                <div>
                                    <?= e(fechaLegible($auditoria['fecha_creacion'])) ?>
                                    <?php if ($auditoria['fecha_actualizacion'] !== $auditoria['fecha_creacion']): ?>
                                        <span class="tabla__secundario">Editada <?= e(fechaLegible($auditoria['fecha_actualizacion'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td data-etiqueta="Acciones">
                                <div class="acciones">
                                    <a class="boton boton--pequeno" href="<?= e(url('/auditorias/editar?id=' . $auditoria['id'])) ?>">
                                        Editar<span class="solo-lectores"> la auditoría número <?= e($auditoria['id']) ?></span>
                                    </a>
                                    <?php // Con JavaScript, confirmar.js pide confirmación; sin él, el formulario se envía igual. ?>
                                    <form method="post" action="<?= e(url('/auditorias/eliminar')) ?>"
                                          data-confirmar="¿Eliminar la auditoría número <?= e($auditoria['id']) ?>? Esta acción no se puede deshacer.">
                                        <input type="hidden" name="csrf" value="<?= e($token) ?>">
                                        <input type="hidden" name="id" value="<?= e($auditoria['id']) ?>">
                                        <button class="boton boton--pequeno boton--peligro" type="submit">
                                            Eliminar<span class="solo-lectores"> la auditoría número <?= e($auditoria['id']) ?></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <script src="<?= e(url('/js/confirmar.js')) ?>" defer></script>
    <?php endif; ?>
</section>
