<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-4" style="max-width: 900px;">
    <h1 class="h3 mb-4">Registrar nuevo dataset</h1>
    <form method="post" action="<?= htmlspecialchars(\App\Support\Url::to('datasets')) ?>">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="title">Título</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="category">Categoría</label>
                        <input type="text" class="form-control" id="category" name="category">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="summary">Resumen</label>
                        <textarea class="form-control" id="summary" name="summary" rows="4" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="keywords">Palabras clave</label>
                        <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Salud, Primera infancia">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="geographic_scope">Cobertura geográfica</label>
                        <input type="text" class="form-control" id="geographic_scope" name="geographic_scope">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="publication_year">Año de publicación</label>
                        <input type="number" class="form-control" id="publication_year" name="publication_year" min="2000" max="2100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="contact_name">Responsable técnico</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="contact_email">Correo de contacto</label>
                        <input type="email" class="form-control" id="contact_email" name="contact_email">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="legal_restrictions">Restricciones legales</label>
                        <textarea class="form-control" id="legal_restrictions" name="legal_restrictions" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="visibility">Visibilidad</label>
                        <select class="form-select" id="visibility" name="visibility">
                            <option value="restricted">Restringido</option>
                            <option value="internal">Interno</option>
                            <option value="public">Público</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default_access_level">Nivel de acceso por defecto</label>
                        <select class="form-select" id="default_access_level" name="default_access_level">
                            <option value="preview">Vista previa</option>
                            <option value="download">Descarga directa</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end">
            <a href="<?= htmlspecialchars(\App\Support\Url::to('/')) ?>" class="btn btn-outline-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar dataset</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
