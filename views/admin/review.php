<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-4" style="max-width: 900px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars(\App\Support\Url::to('admin/requests')) ?>">Solicitudes</a></li>
            <li class="breadcrumb-item active" aria-current="page">Revisión #<?= (int) $request['id'] ?></li>
        </ol>
    </nav>
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong><?= htmlspecialchars($request['dataset_title']) ?></strong>
        </div>
        <div class="card-body">
            <h2 class="h5">Información del solicitante</h2>
            <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></p>
            <p class="mb-1"><strong>Correo:</strong> <?= htmlspecialchars($request['email']) ?></p>
            <p class="mb-1"><strong>Institución:</strong> <?= htmlspecialchars($request['institution'] ?? 'No indicado') ?></p>
            <hr>
            <h2 class="h5">Uso declarado</h2>
            <p><?= nl2br(htmlspecialchars($request['intended_use'])) ?></p>
            <?php if ($request['methodology']): ?>
                <p><strong>Metodología:</strong><br><?= nl2br(htmlspecialchars($request['methodology'])) ?></p>
            <?php endif; ?>
            <?php if ($request['expected_publication']): ?>
                <p><strong>Productos esperados:</strong><br><?= nl2br(htmlspecialchars($request['expected_publication'])) ?></p>
            <?php endif; ?>
            <?php if ($request['safeguards']): ?>
                <p><strong>Medidas de seguridad:</strong><br><?= nl2br(htmlspecialchars($request['safeguards'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= htmlspecialchars(\App\Support\Url::to('admin/requests/' . (int) $request['id'])) ?>">
                <div class="mb-3">
                    <label class="form-label" for="status">Decisión</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="approved">Aprobar</option>
                        <option value="rejected">Rechazar</option>
                        <option value="needs_more_info">Solicitar más información</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="decision_notes">Notas para el solicitante</label>
                    <textarea class="form-control" id="decision_notes" name="decision_notes" rows="3"></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="<?= htmlspecialchars(\App\Support\Url::to('admin/requests')) ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar decisión</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
