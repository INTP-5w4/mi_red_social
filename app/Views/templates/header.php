<div class="header">
    <div class="acciones">
        <?php if (session()->get('logueado')): ?>
            <button type="button" onclick="desplegarModalPublicar()">Publicar</button>
            <a href="<?= base_url('logout') ?>"><button type="button">Log out</button></a>
        <?php else: ?>
            <a href="<?= base_url('login') ?>"><button type="button">Log in</button></a>
        <?php endif; ?>
    </div>

    <?php if (session()->get('logueado')): ?>
        <nav class="nav_logs">
            <a href="<?= base_url('/') ?>">Inicio</a>
            <a href="<?= base_url('logs/likes') ?>">Mis likes</a>
            <a href="<?= base_url('logs/guardados') ?>">Mis guardados</a>
            <a href="<?= base_url('logs/acciones') ?>">Mi actividad</a>
        </nav>

        <div class="publicar" id="modalPublicar">
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

<script>
    // Muestra/oculta el formulario de publicar como modal,
    // igual que el modal de registro en login.php.
    function desplegarModalPublicar() {
        document.getElementById('modalPublicar').classList.toggle('activo');
    }
</script>