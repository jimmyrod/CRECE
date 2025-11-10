<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class FileStorageService
{
    public function store(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir el archivo.');
        }

        $destinationDir = __DIR__ . '/../..' . '/storage/uploads';
        if (!is_dir($destinationDir) && !mkdir($destinationDir, 0775, true) && !is_dir($destinationDir)) {
            throw new RuntimeException('No se pudo crear el directorio de destino.');
        }

        $filename = time() . '_' . bin2hex(random_bytes(4)) . '_' . preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $file['name']);
        $destinationPath = $destinationDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
            throw new RuntimeException('No se pudo mover el archivo subido.');
        }

        return [
            'path' => $destinationPath,
            'uri' => 'storage/uploads/' . $filename,
            'size' => filesize($destinationPath) ?: 0,
            'checksum' => hash_file('sha256', $destinationPath),
        ];
    }
}
