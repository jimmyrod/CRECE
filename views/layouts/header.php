<?php
use App\Support\Auth;
use App\Support\Session;
$user = Auth::user();
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Portal de datos') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">Fundación Crece Contigo</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="/">Datasets</a></li>
                    <?php if (in_array($user['role'], ['administrator', 'internal_researcher'], true)): ?>
                        <li class="nav-item"><a class="nav-link" href="/datasets/create">Nuevo dataset</a></li>
                    <?php endif; ?>
                    <?php if (in_array($user['role'], ['administrator', 'reviewer'], true)): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin/requests">Solicitudes</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($user['first_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted">Rol: <?= htmlspecialchars($user['role']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout">Cerrar sesión</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login">Iniciar sesión</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main>
    <?php if ($success): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
