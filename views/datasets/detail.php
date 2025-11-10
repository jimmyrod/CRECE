<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($dataset['title']) ?></li>
        </ol>
    </nav>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h1 class="h3"><?= htmlspecialchars($dataset['title']) ?></h1>
                    <p class="text-muted mb-2">Visibilidad: <?= htmlspecialchars($dataset['visibility']) ?></p>
                    <p><?= nl2br(htmlspecialchars($dataset['summary'])) ?></p>
                    <dl class="row">
                        <?php if ($dataset['keywords']): ?>
                            <dt class="col-sm-4">Palabras clave</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($dataset['keywords']) ?></dd>
                        <?php endif; ?>
                        <?php if ($dataset['category']): ?>
                            <dt class="col-sm-4">Categoría</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($dataset['category']) ?></dd>
                        <?php endif; ?>
                        <?php if ($dataset['geographic_scope']): ?>
                            <dt class="col-sm-4">Cobertura</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($dataset['geographic_scope']) ?></dd>
                        <?php endif; ?>
                        <?php if ($dataset['publication_year']): ?>
                            <dt class="col-sm-4">Año</dt>
                            <dd class="col-sm-8"><?= htmlspecialchars($dataset['publication_year']) ?></dd>
                        <?php endif; ?>
                        <?php if ($dataset['contact_name'] || $dataset['contact_email']): ?>
                            <dt class="col-sm-4">Contacto</dt>
                            <dd class="col-sm-8">
                                <?= htmlspecialchars($dataset['contact_name'] ?? '') ?><br>
                                <?= htmlspecialchars($dataset['contact_email'] ?? '') ?>
                            </dd>
                        <?php endif; ?>
                        <?php if ($dataset['legal_restrictions']): ?>
                            <dt class="col-sm-4">Restricciones</dt>
                            <dd class="col-sm-8"><?= nl2br(htmlspecialchars($dataset['legal_restrictions'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 mb-0">Versiones disponibles</h2>
                        <?php if ($user && in_array($user['role'], ['administrator', 'internal_researcher'], true)): ?>
                            <a class="btn btn-sm btn-outline-primary" href="/dataset/<?= htmlspecialchars($dataset['slug']) ?>/upload">Subir nueva versión</a>
                        <?php endif; ?>
                    </div>
                    <div class="list-group">
                        <?php foreach ($versions as $version): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="h6 mb-1">Versión <?= htmlspecialchars($version['version_label']) ?></h3>
                                    <p class="mb-0 text-muted">Formato: <?= htmlspecialchars(strtoupper($version['file_format'])) ?> · Subido el <?= htmlspecialchars(date('d/m/Y', strtotime($version['uploaded_at']))) ?></p>
                                    <?php if ($version['change_log']): ?>
                                        <p class="small mb-0">Notas: <?= htmlspecialchars($version['change_log']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <?php if ($user): ?>
                                        <a class="btn btn-primary btn-sm" href="/download/<?= (int) $version['id'] ?>">Descargar</a>
                                    <?php else: ?>
                                        <a class="btn btn-outline-secondary btn-sm" href="/login">Inicia sesión</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$versions): ?>
                            <div class="list-group-item">Aún no se han cargado versiones.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5">Solicitar acceso</h2>
                    <p class="text-muted">Cuéntanos cómo utilizarás la información para que podamos evaluar tu solicitud.</p>
                    <?php if ($user): ?>
                        <form method="post" action="/dataset/<?= htmlspecialchars($dataset['slug']) ?>/request">
                            <div class="mb-3">
                                <label class="form-label" for="intended_use">Uso previsto *</label>
                                <textarea class="form-control" id="intended_use" name="intended_use" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="methodology">Metodología</label>
                                <textarea class="form-control" id="methodology" name="methodology" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="expected_publication">Productos esperados</label>
                                <input type="text" class="form-control" id="expected_publication" name="expected_publication">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="safeguards">Medidas de seguridad</label>
                                <textarea class="form-control" id="safeguards" name="safeguards" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Enviar solicitud</button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn-outline-primary w-100" href="/login">Inicia sesión para solicitar acceso</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
