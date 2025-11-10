<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\App;

class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $stmt = App::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function create(array $data): int
    {
        $stmt = App::db()->prepare(
            'INSERT INTO users (first_name, last_name, email, password_hash, institution, country, role, status, orcid, phone_number)'
            . ' VALUES (:first_name, :last_name, :email, :password_hash, :institution, :country, :role, :status, :orcid, :phone_number)'
        );
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'institution' => $data['institution'] ?? null,
            'country' => $data['country'] ?? null,
            'role' => $data['role'] ?? 'external_researcher',
            'status' => $data['status'] ?? 'pending',
            'orcid' => $data['orcid'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
        ]);

        return (int) App::db()->lastInsertId();
    }

    public function find(int $id): ?array
    {
        $stmt = App::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function pending(): array
    {
        $stmt = App::db()->query('SELECT * FROM users WHERE status = "pending" ORDER BY created_at ASC');
        return $stmt->fetchAll();
    }

    public function recentlyManaged(): array
    {
        $stmt = App::db()->query('SELECT * FROM users WHERE status <> "pending" ORDER BY updated_at DESC LIMIT 10');
        return $stmt->fetchAll();
    }

    public function updateStatusAndRole(int $id, string $status, string $role): bool
    {
        $allowedStatuses = ['pending', 'active', 'suspended'];
        $allowedRoles = ['administrator', 'reviewer', 'internal_researcher', 'external_researcher'];

        if (!in_array($status, $allowedStatuses, true) || !in_array($role, $allowedRoles, true)) {
            return false;
        }

        $stmt = App::db()->prepare('UPDATE users SET status = :status, role = :role, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'status' => $status,
            'role' => $role,
            'id' => $id,
        ]);

        return $stmt->rowCount() > 0;
    }
}
