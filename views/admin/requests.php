<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Solicitudes de acceso</h1>
            <p class="text-muted mb-0">Gestiona las peticiones de investigadores externos.</p>
        </div>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Dataset</th>
                    <th>Solicitante</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td>#<?= (int) $request['id'] ?></td>
                        <td><?= htmlspecialchars($request['dataset_title']) ?></td>
                        <td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?></td>
                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($request['status']) ?></span></td>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($request['submitted_at']))) ?></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars(\App\Support\Url::to('admin/requests/' . (int) $request['id'])) ?>">Revisar</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$requests): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">No hay solicitudes pendientes.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
