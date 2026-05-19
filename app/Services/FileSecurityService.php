<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileSecurityService
{
    // Firmas binarias permitidas
    private static $allowedSignatures = [
        'jpg'  => ["FFD8FF"],
        'jpeg' => ["FFD8FF"],
        'png'  => ["89504E47"],
        'webp' => ["52494646"],
        'pdf'  => ["25504446"],
        'mp4'  => ["00000018", "00000020", "66747970"],
    ];

    public static function validateRealMime(UploadedFile $file): bool
    {
        $path = $file->getRealPath();
        $handle = fopen($path, 'rb');
        $bytes = bin2hex(fread($handle, 20));
        fclose($handle);

        foreach (self::$allowedSignatures as $ext => $signatures) {
            foreach ($signatures as $sig) {
                if (str_starts_with(strtoupper($bytes), $sig)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function sanitizeFilename(string $name): string
    {
        // Quitar rutas, espacios raros, caracteres peligrosos
        $name = basename($name);
        $name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);

        // Evitar doble extensión
        $parts = explode('.', $name);
        if (count($parts) > 2) {
            $ext = array_pop($parts);
            $name = implode('_', $parts) . '.' . $ext;
        }

        return $name;
    }
}
