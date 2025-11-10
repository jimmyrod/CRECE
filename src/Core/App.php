<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class App
{
    private static array $config = [];
    private static ?PDO $pdo = null;

    public static function init(array $config): void
    {
        date_default_timezone_set($config['app']['timezone'] ?? 'UTC');
        self::$config = $config;
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$config;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public static function db(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = self::$config['database'] ?? null;
        if (!$config) {
            throw new RuntimeException('Database configuration missing.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('No se pudo conectar a la base de datos: ' . $exception->getMessage(), 0, $exception);
        }

        return self::$pdo;
    }
}
