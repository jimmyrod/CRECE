<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AccessRequestRepository;
use App\Support\Auth;
use App\Support\Response;
use App\Support\Session;

class AdminController
{
    public function __construct(private readonly AccessRequestRepository $requests)
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
}
