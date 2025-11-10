<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-4" style="max-width: 720px;">
    <h1 class="h3 mb-4">Subir nueva versión</h1>
    <div class="card">
        <div class="card-body">
            <form method="post" action="/dataset/<?= htmlspecialchars($dataset['slug']) ?>/upload" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label" for="version_label">Nombre de la versión</label>
                    <input type="text" class="form-control" id="version_label" name="version_label" value="v<?= date('Ym') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="file_format">Formato</label>
                    <select class="form-select" id="file_format" name="file_format">
                        <option value="csv">CSV</option>
                        <option value="dta">Stata (.dta)</option>
                        <option value="rds">RDS</option>
                        <option value="zip">ZIP</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="file">Archivo</label>
                    <input type="file" class="form-control" id="file" name="file" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="change_log">Notas de la versión</label>
                    <textarea class="form-control" id="change_log" name="change_log" rows="3"></textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <a href="/dataset/<?= htmlspecialchars($dataset['slug']) ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Subir versión</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
