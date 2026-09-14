<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alerta alerta-error">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <p><?= esc($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (empty($publicaciones)): ?>
    <p class="sin-publicaciones">Aún no hay publicaciones.</p>
<?php endif; ?>

<?php foreach ($publicaciones as $publicacion): ?>
    <div class="post">
        <div class="post_header">
            <span class="post_usuario"><?= esc($publicacion['nickname']) ?></span>
        </div>

        <div class="img">
            <img src="<?= base_url('uploads/publicaciones/' . $publicacion['img']) ?>" alt="Publicación de <?= esc($publicacion['nickname']) ?>">
        </div>

        <div class="post_acciones">
            <?php if (session()->get('logueado')): ?>
                <form action="<?= base_url('publicaciones/' . $publicacion['id'] . '/like') ?>" method="post" class="form_accion">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn_like <?= $publicacion['ya_dio_like'] ? 'activo' : '' ?>">
                        <?= $publicacion['ya_dio_like'] ? '♥' : '♡' ?>
                    </button>
                </form>
                <form action="<?= base_url('publicaciones/' . $publicacion['id'] . '/guardar') ?>" method="post" class="form_accion">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn_guardar <?= $publicacion['ya_guardado'] ? 'activo' : '' ?>">
                        <?= $publicacion['ya_guardado'] ? '★' : '☆' ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="post_likes">
            <?= $publicacion['total_likes'] ?> <?= $publicacion['total_likes'] === 1 ? 'like' : 'likes' ?>
        </div>

        <?php if (! empty($publicacion['descripcion'])): ?>
            <div class="post_descripcion">
                <span class="post_usuario"><?= esc($publicacion['nickname']) ?></span>
                <?= esc($publicacion['descripcion']) ?>
            </div>
        <?php endif; ?>

        <?php if (! empty($publicacion['comentarios'])): ?>
            <div class="post_comentarios">
                <?php foreach ($publicacion['comentarios'] as $comentario): ?>
                    <div class="comentario">
                        <span class="post_usuario"><?= esc($comentario['nickname']) ?></span>
                        <?= esc($comentario['descripcion']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (session()->get('logueado')): ?>
            <form action="<?= base_url('publicaciones/' . $publicacion['id'] . '/comentar') ?>" method="post" class="form_comentario">
                <?= csrf_field() ?>
                <input type="text" name="descripcion" maxlength="500" placeholder="Añade un comentario..." required>
                <button type="submit">Publicar</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?= $this->endSection() ?>