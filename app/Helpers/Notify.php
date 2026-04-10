<?php

namespace App\Helpers;

use App\Models\Notification;

class Notify
{
    // Método genérico interno
    private static function sendNotification($userId, $title, $message, $type)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
        ]);
    }

    // Notificación de éxito
    public static function success($userId, $title, $message)
    {
        return self::sendNotification($userId, $title, $message, 'success');
    }

    // Notificación informativa
    public static function info($userId, $title, $message)
    {
        return self::sendNotification($userId, $title, $message, 'info');
    }

    // Notificación de advertencia
    public static function warning($userId, $title, $message)
    {
        return self::sendNotification($userId, $title, $message, 'warning');
    }

    // Notificación de error
    public static function error($userId, $title, $message)
    {
        return self::sendNotification($userId, $title, $message, 'error');
    }
}
