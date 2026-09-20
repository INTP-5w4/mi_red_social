<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>

<h1 class="titulo_log">Mi actividad</h1>

<?php if (empty($registros)): ?>
    <p class="sin-publicaciones">Aún no tienes actividad registrada.</p>
<?php else: ?>
    <table class="tabla_logs">
        <thead>
            <tr>
                <th>Publicación</th>
                <th>Tipo</th>
                <th>Acción</th>
                <th>Fecha</th>
                <th>Quitar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
                <?php
                    if ($registro['origen'] === 'like') {
                        $accionUrl    = 'publicaciones/' . $registro['id_publicacion'] . '/like';
                        $etiquetaTipo = 'Like';
                    } elseif ($registro['origen'] === 'guardado') {
                        $accionUrl    = 'publicaciones/' . $registro['id_publicacion'] . '/guardar';
                        $etiquetaTipo = 'Guardado';
                    } else {
                        $accionUrl    = 'comentarios/' . $registro['id'] . '/eliminar';
                        $etiquetaTipo = 'Comentario';
                    }
                ?>
                <tr>
                    <td>
                        <?php if ($registro['publicacion']): ?>
                            <img class="miniatura_log" src="<?= base_url('uploads/publicaciones/' . $registro['publicacion']['img']) ?>" alt="Publicación">
                        <?php else: ?>
                            <span class="post_usuario">Publicación eliminada</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $etiquetaTipo ?></td>
                    <td class="<?= $registro['origen'] === 'comentario' ? '' : (in_array($registro['tipo_accion'], ['Like', 'Guardar'], true) ? 'accion_positiva' : 'accion_negativa') ?>">
                        <?= $registro['origen'] === 'comentario'
                            ? esc(mb_strimwidth($registro['descripcion'], 0, 60, '…'))
                            : esc($registro['tipo_accion']) ?>
                    </td>
                    <td><?= esc($registro['fecha']) ?></td>
                    <td>
                        <?php if ($registro['activo']): ?>
                            <form action="<?= base_url($accionUrl) ?>" method="post" class="form_accion_log">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn_quitar_log">Quitar</button>
                            </form>
                        <?php else: ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="paginador">
        <?php if ($paginaActual > 1): ?>
            <a href="<?= base_url('logs/acciones') ?>?pagina=<?= $paginaActual - 1 ?>">&laquo; Anterior</a>
        <?php endif; ?>

        <span>Página <?= $paginaActual ?> de <?= $totalPaginas ?></span>

        <?php if ($paginaActual < $totalPaginas): ?>
            <a href="<?= base_url('logs/acciones') ?>?pagina=<?= $paginaActual + 1 ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>