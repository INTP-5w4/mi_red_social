<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>

<h1 class="titulo_log">Mis guardados</h1>

<?php if (session()->getFlashdata('exito')): ?>
    <div class="alerta alerta-exito"><?= esc(session()->getFlashdata('exito')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alerta alerta-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <p><?= esc($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (empty($registros)): ?>
    <p class="sin-publicaciones">Aún no tienes actividad de guardados.</p>
<?php else: ?>
    <table class="tabla_logs">
        <thead>
            <tr>
                <th>Publicación</th>
                <th>Acción</th>
                <th>Fecha</th>
                <th>Quitar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $registro): ?>
                <tr>
                    <td>
                        <img class="miniatura_log" src="<?= base_url('uploads/publicaciones/' . $registro['img']) ?>" alt="Publicación">
                    </td>
                    <td class="<?= $registro['tipo_accion'] === 'Guardar' ? 'accion_positiva' : 'accion_negativa' ?>">
                        <?= esc($registro['tipo_accion']) ?>
                    </td>
                    <td><?= esc($registro['fecha']) ?></td>
                    <td>
                        <?php if ($registro['es_tope']): ?>
                            <form action="<?= base_url('logs/quitar/guardado/' . $registro['id_publicacion']) ?>" method="post" class="form_accion_log">
                                <?= csrf_field() ?>
                                <input type="hidden" name="contexto" value="guardados">
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
        <?= $pager->links('guardados') ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>