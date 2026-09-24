<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use RuntimeException;

/**
 * Gestion des fichiers téléversés : contrôle de type réel (finfo), taille,
 * nom aléatoire et stockage hors de portée d'exécution.
 */
final class UploadService
{
    /** @param array{name:string,tmp_name:string,size:int,error:int,type:string} $file */
    public function store(array $file, string $directory = 'avatars'): string
    {
        $maxSize = (int) Config::get('app.upload.max_size', 5_242_880);

        if (($file['size'] ?? 0) > $maxSize) {
            throw new RuntimeException('Le fichier dépasse la taille maximale autorisée.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
        $allowed = (array) Config::get('app.upload.mimes', []);

        if (!in_array($mime, $allowed, true)) {
            throw new RuntimeException('Type de fichier non autorisé.');
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'application/pdf' => 'pdf',
            default => 'bin',
        };

        $targetDirectory = rtrim((string) Config::get('app.upload.path'), '/') . '/' . trim($directory, '/');

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Impossible de créer le répertoire de destination.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        if (!move_uploaded_file($file['tmp_name'], $targetDirectory . '/' . $filename)) {
            throw new RuntimeException('Échec du téléversement.');
        }

        @chmod($targetDirectory . '/' . $filename, 0644);

        return trim($directory, '/') . '/' . $filename;
    }

    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $path = rtrim((string) Config::get('app.upload.path'), '/') . '/' . ltrim($relativePath, '/');

        // Empêche toute traversée de répertoire.
        if (realpath($path) !== false && str_starts_with((string) realpath($path), (string) realpath((string) Config::get('app.upload.path')))) {
            @unlink($path);
        }
    }
}
