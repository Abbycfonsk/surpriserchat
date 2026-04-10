<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    // Listar notificaciones de un usuario
    public function index($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    // Marcar una notificación como leída
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->read_flag = 1;
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    // Marcar todas como leídas
    public function markAllAsRead($userId)
    {
        Notification::where('user_id', $userId)
            ->update(['read_flag' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }
}
