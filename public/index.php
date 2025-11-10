<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\DatasetController;
use App\Core\App;
use App\Core\Router;
use App\Repositories\AccessRequestRepository;
use App\Repositories\DatasetRepository;
use App\Repositories\DownloadLogRepository;
use App\Repositories\UserRepository;
use App\Services\FileStorageService;
use App\Support\Response;

session_start();

if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $file = __DIR__ . $path;
    if ($path !== '/' && file_exists($file) && !is_dir($file)) {
        return false;
    }
}

// Carga simple de variables desde .env si existe
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, null);
        if ($name === null || $value === null) {
            continue;
        }

        $value = trim($value);
        $value = trim($value, "\"' ");
        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }

    $relative = substr($class, 4);
    $relativePath = __DIR__ . '/../src/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($relativePath)) {
        require_once $relativePath;
    }
});

$config = require __DIR__ . '/../config/app.php';
App::init($config);

$router = new Router();

$authController = new AuthController(new UserRepository());
$datasetController = new DatasetController(
    new DatasetRepository(),
    new AccessRequestRepository(),
    new DownloadLogRepository(),
    new FileStorageService()
);
$adminController = new AdminController(new AccessRequestRepository(), new UserRepository());

$router->get('/', [$datasetController, 'index']);

$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/logout', [$authController, 'logout']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);

$router->get('/datasets/create', [$datasetController, 'createForm']);
$router->post('/datasets', [$datasetController, 'store']);
$router->get('/dataset/{slug}', [$datasetController, 'show']);
$router->get('/dataset/{slug}/upload', [$datasetController, 'uploadForm']);
$router->post('/dataset/{slug}/upload', [$datasetController, 'uploadVersion']);
$router->post('/dataset/{slug}/request', [$datasetController, 'requestAccess']);
$router->get('/download/{id}', [$datasetController, 'download']);

$router->get('/admin/requests', [$adminController, 'dashboard']);
$router->get('/admin/requests/{id}', [$adminController, 'review']);
$router->post('/admin/requests/{id}', [$adminController, 'update']);
$router->get('/admin/users', [$adminController, 'users']);
$router->post('/admin/users/{id}', [$adminController, 'updateUser']);
$router->get('/admin/users/{id}', function (): void {
    Response::redirect('/admin/users');
});

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', rtrim($path, '/') ?: '/');
