<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-5 text-center">
    <h1 class="display-5 fw-bold">Página no encontrada</h1>
    <p class="lead">No pudimos encontrar la ruta solicitada: <code><?= htmlspecialchars($path ?? '') ?></code></p>
    <a class="btn btn-primary" href="<?= htmlspecialchars(\App\Support\Url::to('/')) ?>">Volver al inicio</a>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
