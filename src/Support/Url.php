<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\App;

class Url
{
    public static function base(): string
    {
        $configured = App::config('app.base_url');
        if (is_string($configured) && $configured !== '') {
            return rtrim($configured, '/');
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName !== '') {
            $directory = str_replace('\\', '/', dirname($scriptName));
            if ($directory === '/' || $directory === '\\' || $directory === '.') {
                return '';
            }

            return rtrim($directory, '/');
        }

        return '';
    }

    public static function to(string $path = ''): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            $relative = '';
        } else {
            if (preg_match('#^https?://#i', $path)) {
                return $path;
            }
            $relative = '/' . ltrim($path, '/');
        }

        $base = self::base();
        if ($base === '') {
            return $relative === '' ? '/' : $relative;
        }

        $normalizedBase = rtrim($base, '/');
        if (preg_match('#^https?://#i', $normalizedBase)) {
            return $normalizedBase . ($relative === '' ? '' : $relative);
        }

        return $normalizedBase . ($relative === '' ? '' : $relative);
    }

    public static function asset(string $path): string
    {
        return self::to($path);
    }
}
