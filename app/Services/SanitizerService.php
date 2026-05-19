<?php

namespace App\Services;

class SanitizerService
{
    public static function clean($value)
    {
        if (!$value) return $value;

        // 1. Eliminar etiquetas peligrosas
        $value = strip_tags($value);

        // 2. Eliminar scripts ocultos
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);

        // 3. Eliminar atributos peligrosos (onload, onclick, etc.)
        $value = preg_replace('/on\w+="[^"]*"/i', '', $value);

        // 4. Eliminar caracteres invisibles
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);

        // 5. Trim final
        return trim($value);
    }
}
