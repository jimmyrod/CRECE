<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-5" style="max-width: 720px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Crear cuenta de investigador</h2>
            <form method="post" action="<?= htmlspecialchars(\App\Support\Url::to('register')) ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Correo electrónico institucional</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                    </div>
                    <div class="col-md-6">
                        <label for="institution" class="form-label">Institución</label>
                        <input type="text" class="form-control" id="institution" name="institution">
                    </div>
                    <div class="col-md-6">
                        <label for="country" class="form-label">País</label>
                        <input type="text" class="form-control" id="country" name="country">
                    </div>
                    <div class="col-md-6">
                        <label for="orcid" class="form-label">ORCID</label>
                        <input type="text" class="form-control" id="orcid" name="orcid">
                    </div>
                    <div class="col-md-6">
                        <label for="phone_number" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number">
                    </div>
                </div>
                <div class="form-text mt-3">Tu cuenta permanecerá en estado pendiente hasta que el equipo la apruebe.</div>
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success">Enviar registro</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
