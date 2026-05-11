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
public function sendAdsSummary()
{
    $geniuses = User::where('role', 'genius')->get();

    foreach ($geniuses as $genius) {

        $skills = $genius->skills->pluck('id')->toArray();

        $ads = SurpriseAd::with('surprise')
            ->where('is_active', 1)
            ->whereNull('notified_at')
            ->where('expires_at', '>', now())
            ->get()
            ->filter(function ($ad) use ($skills) {
                return in_array($ad->surprise->skill_id, $skills);
            });

        if ($ads->isEmpty()) {
            continue;
        }

        $premium = $ads->where('priority', 3)->count();
        $normal = $ads->where('priority', '<', 3)->count();

        if ($premium > 0) {
            $msg = "Hay $premium sorpresas Premium y $normal destacadas nuevas que encajan contigo.";
        } else {
            $msg = "Hay $normal nuevas sorpresas destacadas que encajan contigo.";
        }

        NotificationEvents::adsSummary($genius, $msg);

        foreach ($ads as $ad) {
            $ad->notified_at = now();
            $ad->save();
        }
    }
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
