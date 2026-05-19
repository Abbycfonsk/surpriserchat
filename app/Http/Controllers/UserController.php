<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Surprise;
use App\Models\Review;
use App\Models\UserSkill;
use App\Services\AuditService;
use App\Services\SanitizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function dashboard($userId)
    {
        // 1. Usuario
        $user = User::findOrFail($userId);

        // 2. Skills + progreso
        $skills = UserSkill::where('user_id', $userId)
            ->with('skill')
            ->get()
            ->map(function ($item) {
                return [
                    'skill_id' => $item->skill_id,
                    'skill_name' => $item->skill->name,
                    'level' => $item->level,
                    'progress' => $item->progress,
                    'xp_total' => $item->xp_total,
                ];
            });

        // 3. Sorpresas creadas
        $created = Surprise::where('creator_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $createdStats = [
            'total' => Surprise::where('creator_id', $userId)->count(),
            'open' => Surprise::where('creator_id', $userId)->where('status', 'open')->count(),
            'in_progress' => Surprise::where('creator_id', $userId)->where('status', 'in_progress')->count(),
            'delivered' => Surprise::where('creator_id', $userId)->where('status', 'delivered')->count(),
            'completed' => Surprise::where('creator_id', $userId)->where('status', 'completed')->count(),
        ];

        // 4. Sorpresas realizadas como genius
        $done = Surprise::where('genius_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $doneStats = [
            'total' => Surprise::where('genius_id', $userId)->count(),
            'in_progress' => Surprise::where('genius_id', $userId)->where('status', 'in_progress')->count(),
            'delivered' => Surprise::where('genius_id', $userId)->where('status', 'delivered')->count(),
            'completed' => Surprise::where('genius_id', $userId)->where('status', 'completed')->count(),
        ];

        // 5. Reviews recibidas
        $reviews = Review::where('reviewed_user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $rating = Review::where('reviewed_user_id', $userId)->avg('rating_genius');

        // 6. Nivel global (simple)
        $globalLevel = $skills->avg('level') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'skills' => $skills,
                'created_surprises' => [
                    'latest' => $created,
                    'stats' => $createdStats
                ],
                'done_surprises' => [
                    'latest' => $done,
                    'stats' => $doneStats
                ],
                'reviews' => [
                    'latest' => $reviews,
                    'rating' => $rating ? round($rating, 2) : 0
                ],
                'global' => [
                    'global_level' => round($globalLevel, 2),
                    'total_skills' => count($skills),
                    'total_reviews' => Review::where('reviewed_user_id', $userId)->count(),
                    'total_surprises' => $createdStats['total'] + $doneStats['total']
                ]
            ]
        ]);
    }

    public function updateProfile(Request $request)
{
    $user = $request->user();

    // Validación
    $validated = $request->validate([
        'name' => 'nullable|string|max:255',
        'username' => 'nullable|string|max:100|unique:users,username,' . $user->id,
        'bio' => 'nullable|string|max:500',
        'phone' => 'nullable|string|max:50',
        'location_city' => 'nullable|string|max:100',
        'location_country' => 'nullable|string|max:100',
        'is_genius' => 'nullable|in:0,1', // FormData no acepta boolean
        'avatar' => 'nullable|image|max:2048',
    ]);

    // Sanitizar texto libre
    foreach (['name', 'username', 'bio', 'phone', 'location_city', 'location_country'] as $field) {
        if (isset($validated[$field])) {
            $validated[$field] = SanitizerService::clean($validated[$field]);
        }
    }

    // Convertir is_genius manualmente (FormData envía "0" o "1")
    if ($request->has('is_genius')) {
        $validated['is_genius'] = $request->input('is_genius') == "1" ? 1 : 0;
    }

    // ❗ BLOQUEAR desactivar genio si tiene sorpresas activas
    if ($request->has('is_genius') && $validated['is_genius'] == 0) {

        $activeSurprises = \App\Models\Surprise::where('genius_id', $user->id)
            ->whereIn('status', ['open', 'in_progress', 'delivered', 'revision_requested'])
            ->count();

        if ($activeSurprises > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu perfil de genio mientras tengas sorpresas activas.'
            ], 400);
        }
    }

    // Guardar valores antiguos para auditoría
    $old = $user->getOriginal();

    // Subida de avatar
   if ($request->hasFile('avatar')) {
    if ($user->avatar) {
        $oldPath = str_replace('/storage/', '', $user->avatar);
        Storage::disk('public')->delete($oldPath);
    }

    $path = $request->file('avatar')->store('avatars', 'public');
    $validated['avatar'] = $path;
}
    

    // Actualizar usuario
    $user->update($validated);

    // Auditoría
    AuditService::log(
        'user_profile_updated',
        'User',
        $user->id,
        $old,
        $user->getChanges()
    );

    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => $user
    ]);
}
}
