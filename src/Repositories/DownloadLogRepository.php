<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\App;

class DownloadLogRepository
{
    public function create(array $data): void
    {
        App::db()->prepare('INSERT INTO download_audit_logs (dataset_version_id, access_request_id, user_id, download_token, ip_address, user_agent)
            VALUES (:dataset_version_id, :access_request_id, :user_id, :download_token, :ip_address, :user_agent)')->execute([
                'dataset_version_id' => $data['dataset_version_id'],
                'access_request_id' => $data['access_request_id'] ?? null,
                'user_id' => $data['user_id'],
                'download_token' => $data['download_token'],
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
            ]);
    }
}
