<?php

declare(strict_types=1);

namespace App\Support;

class Text
{
    public static function slug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        $value = preg_replace('/[^a-zA-Z0-9]+/', '-', $value ?? '');
        $value = trim((string) $value, '-');
        return strtolower($value ?: bin2hex(random_bytes(4)));
    }
}
