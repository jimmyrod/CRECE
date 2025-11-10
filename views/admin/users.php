<?php
use App\Support\Url;
include __DIR__ . '/../layouts/header.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Gestión de usuarios</h1>
            <p class="text-muted mb-0">Aprueba nuevos registros y administra roles recientes.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?= htmlspecialchars(Url::to('admin/requests')) ?>">Ver solicitudes de datasets</a>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>Pendientes de aprobación</strong>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Institución</th>
                    <th>País</th>
                    <th class="text-end">Gestión</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($pending as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['institution'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($user['country'] ?? '—') ?></td>
                        <td class="text-end">
                            <form class="row row-cols-lg-auto g-2 align-items-center justify-content-end" method="post" action="<?= htmlspecialchars(Url::to('admin/users/' . (int) $user['id'])) ?>">
                                <div class="col">
                                    <select name="role" class="form-select form-select-sm">
                                        <option value="external_researcher" selected>Investigador externo</option>
                                        <option value="internal_researcher">Investigador interno</option>
                                        <option value="reviewer">Revisor</option>
                                        <option value="administrator">Administrador</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Aprobar</button>
                                </div>
                                <div class="col">
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger">Rechazar</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pending): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No hay usuarios pendientes.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <strong>Últimos cambios</strong>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Actualizado</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recent as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge bg-success">Activa</span>
                            <?php elseif ($user['status'] === 'suspended'): ?>
                                <span class="badge bg-danger">Suspendida</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($user['updated_at'] ?? $user['created_at']))) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recent): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Aún no hay historial de aprobaciones.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
