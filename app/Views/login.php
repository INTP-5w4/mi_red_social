<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="<?= base_url('estilos/style.css') ?>">
</head>
<body class="pagina-login">

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

    <div class="form">
        <form action="<?= base_url('login') ?>" method="post">
            <?= csrf_field() ?>
            <label for="correo">Correo</label>
            <input type="email" name="correo" id="correo" value="<?= esc(old('correo')) ?>" required>
            <label for="contrasenia">Contraseña</label>
            <input type="password" name="contrasenia" id="contrasenia" required>
            <input type="submit" value="Enviar">
        </form>
        <div class="registro">
            <button onclick="desplegar_modal()" type="button">Registrarse</button>
        </div>
    </div>

    <div class="modal_registro" id="modalRegistro">
        <form action="<?= base_url('registro') ?>" method="post">
            <?= csrf_field() ?>
            <label for="usuario">Usuario</label>
            <input type="text" name="usuario" id="usuario" value="<?= esc(old('usuario')) ?>" required>
            <label for="correoRegistro">Correo</label>
            <input type="email" name="correo" id="correoRegistro" required>
            <label for="contraseniaRegistro">Contraseña</label>
            <input type="password" name="contrasenia" id="contraseniaRegistro" required>
            <input type="submit" value="Enviar">
        </form>
    </div>

    <script>
        // Muestra/oculta el modal de registro sumando/quitando una clase,
        // en vez de manipular display en línea
        function desplegar_modal() {
            document.getElementById('modalRegistro').classList.toggle('activo');
        }
    </script>
</body>
</html>