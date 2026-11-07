<section class="portada">
    <p class="antetitulo">Rendimiento y QA</p>
    <h1>Registro de auditorías de landing pages</h1>
    <p class="entradilla">
        Cada auditoría guarda las métricas Core Web Vitals de una página y el resultado de su
        checklist de calidad, para saber qué páginas cumplen y cuáles necesitan trabajo.
    </p>
    <p class="portada__accion"><a class="boton boton--primario" href="<?= e(url('/auditorias/nueva')) ?>">Registrar una auditoría</a></p>
</section>

<section aria-labelledby="titulo-metricas">
    <h2 id="titulo-metricas">Qué se mide en cada auditoría</h2>

    <ul class="tarjetas">
        <li class="tarjeta">
            <h3>LCP</h3>
            <p class="tarjeta__valor">≤ 2,5 s</p>
            <p>Tiempo de carga del elemento principal de la página.</p>
        </li>
        <li class="tarjeta">
            <h3>INP</h3>
            <p class="tarjeta__valor">≤ 200 ms</p>
            <p>Tiempo de respuesta a las interacciones del usuario.</p>
        </li>
        <li class="tarjeta">
            <h3>CLS</h3>
            <p class="tarjeta__valor">≤ 0,1</p>
            <p>Estabilidad visual: cuánto se mueve el contenido al cargar.</p>
        </li>
        <li class="tarjeta">
            <h3>Checklist QA</h3>
            <p class="tarjeta__valor">17 puntos</p>
            <p>Revisión de calidad previa a la publicación de la página.</p>
        </li>
    </ul>

    <p class="nota">Los valores de LCP, INP y CLS son los umbrales que Core Web Vitals considera buenos.</p>
</section>

<section class="seccion-inicio" aria-labelledby="titulo-clasificacion">
    <h2 id="titulo-clasificacion">Cómo se clasifica cada auditoría</h2>

    <div class="tabla-simple">
        <table>
            <caption class="solo-lectores">Umbrales de cada métrica</caption>
            <thead>
                <tr>
                    <th scope="col">Métrica</th>
                    <th scope="col">Buena</th>
                    <th scope="col">Por mejorar</th>
                    <th scope="col">Deficiente</th>
                </tr>
            </thead>
            <tbody>
                <tr><th scope="row">LCP</th><td>≤&nbsp;2,5&nbsp;s</td><td>≤&nbsp;4&nbsp;s</td><td>&gt;&nbsp;4&nbsp;s</td></tr>
                <tr><th scope="row">INP</th><td>≤&nbsp;200&nbsp;ms</td><td>≤&nbsp;500&nbsp;ms</td><td>&gt;&nbsp;500&nbsp;ms</td></tr>
                <tr><th scope="row">CLS</th><td>≤&nbsp;0,1</td><td>≤&nbsp;0,25</td><td>&gt;&nbsp;0,25</td></tr>
            </tbody>
        </table>
    </div>

    <ul class="reglas">
        <li><span class="estado estado--aprobada">Aprobada</span> Las tres métricas son buenas.</li>
        <li><span class="estado estado--mejorable">Mejorable</span> Ninguna es deficiente, pero alguna está por mejorar.</li>
        <li><span class="estado estado--critica">Crítica</span> Al menos una métrica es deficiente.</li>
    </ul>

    <p class="nota">El checklist de 17 puntos se informa aparte y no cambia la clasificación. El porcentaje de aprobación es la proporción de auditorías aprobadas.</p>
</section>
