<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-5" style="max-width: 480px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-3">Iniciar sesión</h2>
            <form method="post" action="/login">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </div>
            </form>
            <p class="mt-3 mb-0">¿No tienes cuenta? <a href="/register">Regístrate aquí</a>.</p>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
