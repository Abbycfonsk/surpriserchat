<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSkill;
use App\Models\Review;
use App\Models\Surprise;

class GeniusController extends Controller
{
    public function dashboard($geniusId)
{
    // Últimas 10 sorpresas completadas por este genio
    $surprises = Surprise::with([
        'skill:id,name',
        'review:id,surprise_id,rating,comment',
        'files:id,surprise_id,file_path,file_type'
    ])
    ->where('genius_id', $geniusId)
    ->where('status', 'completed')
    ->orderBy('completed_at', 'desc')
    ->limit(10)
    ->get([
        'id',
        'title',
        'description',
        'size',
        'skill_id',
        'started_at',
        'delivered_at',
        'completed_at',
        'hours_spent',
        'final_price'
    ]);

    return response()->json([
        'success' => true,
        'data' => $surprises
    ]);
}
public function feed(Request $request)
{
    $genius = $request->user();

    // Skills del genio
    $skills = $genius->skills->pluck('id')->toArray();
    $now = now();

    // Obtener sorpresas con ads activos
    $surprises = Surprise::with([
        'ads' => function ($q) {
            $q->where('is_active', 1)
              ->orderBy('priority', 'desc'); // el ad más importante primero
        }
    ])
    ->where('status', 'open')
    ->whereIn('skill_id', $skills)
    ->get()
    ->filter(function ($s) use ($now) {

        // Si no tiene ads → sorpresa normal → visible
        if ($s->ads->isEmpty()) {
            return true;
        }

        // Tomar el ad activo con mayor prioridad
        $ad = $s->ads->first();

        // Si expiró → no mostrar
        if ($ad->expires_at->isPast()) {
            return false;
        }

        // Early access
        $early = $ad->early_access_hours ?? 0;

        if ($early > 0) {
            $visibleAt = $ad->activated_at->copy()->addHours($early);

            // Si aún no es visible → ocultar
            if ($now->lt($visibleAt)) {
                return false;
            }
        }

        return true;
    })
    ->sortByDesc(function ($s) {
        $ad = $s->ads->first();

        return [
            $s->is_urgent ? 1 : 0,          // urgentes primero
            $ad->priority ?? 0,             // prioridad del plan
            $s->created_at->timestamp       // más recientes después
        ];
    })
    ->values();

    return response()->json([
        'success' => true,
        'data' => $surprises
    ]);
}
    public function suggest($skillId)
    {
        // 1. Genius que tienen esta skill
        $geniuses = UserSkill::where('skill_id', $skillId)
            ->with('user')
            ->get()
            ->map(function ($item) {
                $userId = $item->user_id;

                // Reputación media
                $rating = Review::where('reviewed_user_id', $userId)->avg('rating_genius');

                // Sorpresas completadas
                $completed = Surprise::where('genius_id', $userId)
                    ->where('status', 'completed')
                    ->count();

                return [
                    'user_id' => $userId,
                    'name' => $item->user->name,
                    'avatar' => $item->user->avatar,
                    'skill_level' => $item->level,
                    'skill_progress' => $item->progress,
                    'rating' => $rating ? round($rating, 2) : 0,
                    'completed_surprises' => $completed,
                ];
            });

        // 2. Ordenar por:
        // - nivel de skill
        // - reputación
        // - sorpresas completadas
        $sorted = $geniuses->sortByDesc(function ($g) {
            return [
                $g['skill_level'],
                $g['rating'],
                $g['completed_surprises']
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $sorted
        ]);
    }
}
