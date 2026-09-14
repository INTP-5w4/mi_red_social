<div class="header">
    <div class="acciones">
        <?php if (session()->get('logueado')): ?>
            <a href="<?= base_url('logout') ?>"><button type="button">Log out</button></a>
        <?php else: ?>
            <a href="<?= base_url('login') ?>"><button type="button">Log in</button></a>
        <?php endif; ?>
    </div>

    <?php if (session()->get('logueado')): ?>
        <div class="publicar">
            <form action="<?= base_url('publicaciones/crear') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <label for="img">Imagen</label>
                <input type="file" name="img" id="img" accept="image/*" required>
                <label for="descripcion">Descripción</label>
                <input type="text" name="descripcion" id="descripcion" maxlength="2200" placeholder="Escribe una descripción...">
                <button type="submit">Publicar</button>
            </form>
        </div>
    <?php endif; ?>
</div>