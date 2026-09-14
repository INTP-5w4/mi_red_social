<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? 'Red Social' ?></title>
    <link rel="stylesheet" href="<?= base_url('estilos/style.css') ?>">
</head>
<body>
    <?= $this->include('templates/header') ?>

    <main class="contenido">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('templates/footer') ?>
</body>
</html>