<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AccessRequestRepository;
use App\Repositories\UserRepository;
use App\Support\Auth;
use App\Support\Response;
use App\Support\Session;

class AdminController
{
    public function __construct(
        private readonly AccessRequestRepository $requests,
        private readonly UserRepository $users
    )
    {
    }

    public function dashboard(): void
    {
        Auth::requireRole(['administrator', 'reviewer']);
        $requests = $this->requests->pending();
        Response::view('admin/requests', compact('requests'));
    }

    public function review(int $id): void
    {
        Auth::requireRole(['administrator', 'reviewer']);
        $request = $this->requests->find($id);
        if (!$request) {
            Response::view('errors/404', ['path' => 'admin/request'], 404);
            return;
        }

        Response::view('admin/review', compact('request'));
    }

    public function update(int $id): void
    {
        Auth::requireRole(['administrator', 'reviewer']);
        $status = $_POST['status'] ?? 'submitted';
        $notes = trim($_POST['decision_notes'] ?? '');

        if (!in_array($status, ['approved', 'rejected', 'needs_more_info'], true)) {
            Session::flash('error', 'Estado inválido.');
            Response::redirect('/admin/requests/' . $id);
            return;
        }

        $user = Auth::user();
        $this->requests->updateStatus($id, $status, $notes ?: null, $user['id']);
        Session::flash('success', 'Solicitud actualizada.');
        Response::redirect('/admin/requests');
    }

    public function users(): void
    {
        Auth::requireRole(['administrator']);
        $pending = $this->users->pending();
        $recent = $this->users->recentlyManaged();

        Response::view('admin/users', [
            'pending' => $pending,
            'recent' => $recent,
        ]);
    }

    public function updateUser(int $id): void
    {
        Auth::requireRole(['administrator']);

        $action = $_POST['action'] ?? '';
        $role = $_POST['role'] ?? 'external_researcher';

        $allowedRoles = ['administrator', 'reviewer', 'internal_researcher', 'external_researcher'];
        if (!in_array($role, $allowedRoles, true)) {
            Session::flash('error', 'Rol inválido seleccionado.');
            Response::redirect('/admin/users');
            return;
        }

        $status = match ($action) {
            'approve' => 'active',
            'reject' => 'suspended',
            default => null,
        };

        if ($status === null) {
            Session::flash('error', 'Acción inválida.');
            Response::redirect('/admin/users');
            return;
        }

        $currentUser = Auth::user();
        if ($currentUser && $currentUser['id'] === $id && $status !== 'active') {
            Session::flash('error', 'No puedes suspender tu propia cuenta.');
            Response::redirect('/admin/users');
            return;
        }

        if (!$this->users->updateStatusAndRole($id, $status, $role)) {
            Session::flash('error', 'No se pudo actualizar al usuario seleccionado.');
            Response::redirect('/admin/users');
            return;
        }

        $message = $status === 'active'
            ? 'Usuario aprobado y activado correctamente.'
            : 'Usuario marcado como suspendido.';

        Session::flash('success', $message);
        Response::redirect('/admin/users');
    }
}
