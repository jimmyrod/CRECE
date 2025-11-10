<?php

declare(strict_types=1);

namespace App\Support;

class Response
{
    public static function redirect(string $path): void
    {
        header('Location: ' . Url::to($path));
        exit;
    }

    public static function view(string $template, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data);
        $viewPath = __DIR__ . '/../..' . '/views/' . ltrim($template, '/');
        if (!str_ends_with($viewPath, '.php')) {
            $viewPath .= '.php';
        }

        if (!file_exists($viewPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }

        include $viewPath;
    }
}
