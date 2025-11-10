<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\App;

class AccessRequestRepository
{
    public function create(array $data): int
    {
        $stmt = App::db()->prepare('INSERT INTO access_requests (dataset_id, requester_id, intended_use, methodology, institution, expected_publication, safeguards, agreement_version, status)
            VALUES (:dataset_id, :requester_id, :intended_use, :methodology, :institution, :expected_publication, :safeguards, :agreement_version, :status)');
        $stmt->execute([
            'dataset_id' => $data['dataset_id'],
            'requester_id' => $data['requester_id'],
            'intended_use' => $data['intended_use'],
            'methodology' => $data['methodology'] ?? null,
            'institution' => $data['institution'] ?? null,
            'expected_publication' => $data['expected_publication'] ?? null,
            'safeguards' => $data['safeguards'] ?? null,
            'agreement_version' => $data['agreement_version'] ?? 'v1',
            'status' => 'submitted',
        ]);

        return (int) App::db()->lastInsertId();
    }

    public function pending(): array
    {
        $stmt = App::db()->query('SELECT ar.*, d.title AS dataset_title, u.first_name, u.last_name FROM access_requests ar JOIN datasets d ON ar.dataset_id = d.id JOIN users u ON ar.requester_id = u.id WHERE ar.status IN ("submitted", "in_review") ORDER BY ar.submitted_at DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = App::db()->prepare('SELECT ar.*, d.title AS dataset_title, d.slug AS dataset_slug, u.first_name, u.last_name, u.email FROM access_requests ar JOIN datasets d ON ar.dataset_id = d.id JOIN users u ON ar.requester_id = u.id WHERE ar.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateStatus(int $id, string $status, ?string $notes, int $reviewerId): void
    {
        $pdo = App::db();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE access_requests SET status = :status WHERE id = :id')->execute([
            'status' => $status,
            'id' => $id,
        ]);

        $pdo->prepare('INSERT INTO access_request_reviews (access_request_id, reviewer_id, decision, decision_notes)
            VALUES (:access_request_id, :reviewer_id, :decision, :decision_notes)')->execute([
                'access_request_id' => $id,
                'reviewer_id' => $reviewerId,
                'decision' => $status === 'approved' ? 'approved' : ($status === 'needs_more_info' ? 'needs_more_info' : 'rejected'),
                'decision_notes' => $notes,
            ]);

        if ($status === 'approved') {
            $request = $this->find($id);
            if ($request) {
                $pdo->prepare('INSERT INTO access_agreements (access_request_id, dataset_id, requester_id, agreement_text, signature_ip)
                    VALUES (:access_request_id, :dataset_id, :requester_id, :agreement_text, :signature_ip)')->execute([
                        'access_request_id' => $id,
                        'dataset_id' => $request['dataset_id'],
                        'requester_id' => $request['requester_id'],
                        'agreement_text' => 'El solicitante acepta cumplir con las políticas de uso de datos de la Fundación.',
                        'signature_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                    ]);
            }
        }

        $pdo->commit();
    }
}
