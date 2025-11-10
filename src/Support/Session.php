<?php

declare(strict_types=1);

namespace App\Support;

class Session
{
    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message === null) {
            $value = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $value;
        }

        $_SESSION['_flash'][$key] = $message;
        return null;
    }
}
