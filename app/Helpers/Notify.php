<?php

namespace App\Helpers;

use App\Models\Notification;

class Notify
{
    public static function send($userId, $title, $message, $type = 'info', array $metadata = null)
    {
        return Notification::create([
            'user_id'    => $userId,
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'metadata'   => $metadata,
            'read_flag'  => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function info($userId, $title, $message, array $metadata = null)
    {
        return self::send($userId, $title, $message, 'info', $metadata);
    }

    public static function success($userId, $title, $message, array $metadata = null)
    {
        return self::send($userId, $title, $message, 'success', $metadata);
    }

    public static function warning($userId, $title, $message, array $metadata = null)
    {
        return self::send($userId, $title, $message, 'warning', $metadata);
    }

    public static function error($userId, $title, $message, array $metadata = null)
    {
        return self::send($userId, $title, $message, 'error', $metadata);
    }
}
