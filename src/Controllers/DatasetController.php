<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AccessRequestRepository;
use App\Repositories\DatasetRepository;
use App\Repositories\DownloadLogRepository;
use App\Services\FileStorageService;
use App\Support\Auth;
use App\Support\Response;
use App\Support\Session;

class DatasetController
{
    public function __construct(
        private readonly DatasetRepository $datasets,
        private readonly AccessRequestRepository $requests,
        private readonly DownloadLogRepository $downloads,
        private readonly FileStorageService $storage
    ) {
    }

    public function index(): void
    {
        $user = Auth::user();
        $datasets = $this->datasets->allVisible($user);
        Response::view('datasets/index', compact('datasets', 'user'));
    }

    public function show(string $slug): void
    {
        $dataset = $this->datasets->findBySlug($slug);
        if (!$dataset) {
            Response::view('errors/404', ['path' => $slug], 404);
            return;
        }

        $versions = $this->datasets->versionsForDataset((int) $dataset['id']);
        $user = Auth::user();
        Response::view('datasets/detail', compact('dataset', 'versions', 'user'));
    }

    public function createForm(): void
    {
        Auth::requireRole(['administrator', 'internal_researcher']);
        Response::view('datasets/create');
    }

    public function store(): void
    {
        Auth::requireRole(['administrator', 'internal_researcher']);
        $user = Auth::user();
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        if (!$title || !$summary) {
            Session::flash('error', 'El título y resumen son obligatorios.');
            Response::redirect('/datasets/create');
            return;
        }

        $dataset = $this->datasets->create([
            'title' => $title,
            'summary' => $summary,
            'keywords' => trim($_POST['keywords'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'geographic_scope' => trim($_POST['geographic_scope'] ?? ''),
            'publication_year' => trim($_POST['publication_year'] ?? ''),
            'contact_name' => trim($_POST['contact_name'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'legal_restrictions' => trim($_POST['legal_restrictions'] ?? ''),
            'visibility' => $_POST['visibility'] ?? 'restricted',
            'storage_uri' => 'storage/uploads',
            'default_access_level' => $_POST['default_access_level'] ?? 'preview',
            'created_by' => $user['id'],
        ]);

        Session::flash('success', 'Dataset creado. Puedes subir versiones.');
        Response::redirect('/dataset/' . $dataset['slug']);
    }

    public function uploadForm(string $slug): void
    {
        $dataset = $this->datasets->findBySlug($slug);
        if (!$dataset) {
            Response::view('errors/404', ['path' => $slug], 404);
            return;
        }

        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['administrator', 'internal_researcher'], true)) {
            Session::flash('error', 'No tienes permisos para subir versiones.');
            Response::redirect('/dataset/' . $slug);
            return;
        }

        Response::view('datasets/upload', compact('dataset'));
    }

    public function uploadVersion(string $slug): void
    {
        $dataset = $this->datasets->findBySlug($slug);
        if (!$dataset) {
            Response::view('errors/404', ['path' => $slug], 404);
            return;
        }

        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['administrator', 'internal_researcher'], true)) {
            Session::flash('error', 'No tienes permisos para subir versiones.');
            Response::redirect('/dataset/' . $slug);
            return;
        }

        if (!isset($_FILES['file'])) {
            Session::flash('error', 'Debes seleccionar un archivo.');
            Response::redirect('/dataset/' . $slug . '/upload');
            return;
        }

        try {
            $stored = $this->storage->store($_FILES['file']);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect('/dataset/' . $slug . '/upload');
            return;
        }

        $versionId = $this->datasets->createVersion([
            'dataset_id' => (int) $dataset['id'],
            'version_label' => trim($_POST['version_label'] ?? 'v1'),
            'file_name' => $_FILES['file']['name'],
            'file_format' => $_POST['file_format'] ?? 'other',
            'file_size_bytes' => $stored['size'],
            'checksum' => $stored['checksum'],
            'storage_uri' => $stored['uri'],
            'change_log' => trim($_POST['change_log'] ?? ''),
            'uploaded_by' => $user['id'],
        ]);

        Session::flash('success', 'Versión subida correctamente.');
        Response::redirect('/dataset/' . $slug);
    }

    public function requestAccess(string $slug): void
    {
        $dataset = $this->datasets->findBySlug($slug);
        if (!$dataset) {
            Response::view('errors/404', ['path' => $slug], 404);
            return;
        }

        if (!Auth::check()) {
            Session::flash('error', 'Inicia sesión para solicitar acceso.');
            Response::redirect('/login');
            return;
        }

        $data = [
            'intended_use' => trim($_POST['intended_use'] ?? ''),
            'methodology' => trim($_POST['methodology'] ?? ''),
            'institution' => trim($_POST['institution'] ?? ''),
            'expected_publication' => trim($_POST['expected_publication'] ?? ''),
            'safeguards' => trim($_POST['safeguards'] ?? ''),
        ];

        if (!$data['intended_use']) {
            Session::flash('error', 'Describe el uso previsto.');
            Response::redirect('/dataset/' . $slug);
            return;
        }

        $user = Auth::user();
        $this->requests->create([
            'dataset_id' => (int) $dataset['id'],
            'requester_id' => $user['id'],
            'intended_use' => $data['intended_use'],
            'methodology' => $data['methodology'],
            'institution' => $data['institution'] ?: $user['institution'] ?? null,
            'expected_publication' => $data['expected_publication'],
            'safeguards' => $data['safeguards'],
            'agreement_version' => 'v1',
        ]);

        Session::flash('success', 'Solicitud enviada. El equipo revisará tu petición.');
        Response::redirect('/dataset/' . $slug);
    }

    public function download(int $versionId): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debes iniciar sesión para descargar.');
            Response::redirect('/login');
            return;
        }

        $version = $this->datasets->findVersion($versionId);
        if (!$version) {
            Response::view('errors/404', ['path' => 'download'], 404);
            return;
        }

        $user = Auth::user();
        $allowed = false;

        if (in_array($user['role'], ['administrator', 'reviewer', 'internal_researcher'], true)) {
            $allowed = true;
        }

        if ($version['visibility'] === 'public') {
            $allowed = true;
        }

        if (!$allowed) {
            $pdo = \App\Core\App::db();
            $stmt = $pdo->prepare('SELECT status FROM access_requests WHERE dataset_id = :dataset_id AND requester_id = :requester_id AND status = "approved" LIMIT 1');
            $stmt->execute([
                'dataset_id' => $version['dataset_id'],
                'requester_id' => $user['id'],
            ]);
            if ($stmt->fetch()) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            Session::flash('error', 'Tu solicitud aún no ha sido aprobada.');
            Response::redirect('/dataset/' . $version['dataset_slug']);
            return;
        }

        $filePath = __DIR__ . '/../..' . '/' . $version['storage_uri'];
        if (!file_exists($filePath)) {
            Session::flash('error', 'Archivo no encontrado en el servidor.');
            Response::redirect('/dataset/' . $version['dataset_slug']);
            return;
        }

        $this->downloads->create([
            'dataset_version_id' => $versionId,
            'access_request_id' => null,
            'user_id' => $user['id'],
            'download_token' => bin2hex(random_bytes(16)),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($version['file_name']) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
