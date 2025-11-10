<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Catálogo de datasets</h1>
            <p class="text-muted mb-0">Explora las investigaciones disponibles en la Fundación.</p>
        </div>
        <?php if ($user && in_array($user['role'], ['administrator', 'internal_researcher'], true)): ?>
            <a class="btn btn-primary" href="/datasets/create">Registrar nuevo dataset</a>
        <?php endif; ?>
    </div>
    <div class="row g-3">
        <?php foreach ($datasets as $dataset): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h5">
                            <a href="/dataset/<?= htmlspecialchars($dataset['slug']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars($dataset['title']) ?>
                            </a>
                        </h2>
                        <p class="text-muted flex-grow-1"><?= htmlspecialchars(mb_strimwidth($dataset['summary'], 0, 160, '…')) ?></p>
                        <div class="mt-3">
                            <span class="badge bg-secondary me-1">Visibilidad: <?= htmlspecialchars($dataset['visibility']) ?></span>
                            <?php if ($dataset['category']): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($dataset['category']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$datasets): ?>
            <div class="col-12">
                <div class="alert alert-info">No hay datasets registrados aún.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
