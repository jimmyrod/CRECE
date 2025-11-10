<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\App;

class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function attempt(string $email, string $password): bool
    {
        $pdo = App::db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        if ($user['status'] !== 'active' && $user['role'] !== 'administrator') {
            return false;
        }

        $_SESSION['user'] = $user;
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
    }

    public static function requireRole(array $roles): void
    {
        $user = self::user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            Response::redirect('login');
        }
    }
}
