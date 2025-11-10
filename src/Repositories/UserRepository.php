<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\App;
use PDO;

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
        $stmt = App::db()->prepare('INSERT INTO users (first_name, last_name, email, password_hash, institution, country, role, status, orcid, phone_number)
            VALUES (:first_name, :last_name, :email, :password_hash, :institution, :country, :role, :status, :orcid, :phone_number)');
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
}
