<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSkill;
use App\Models\Review;
use App\Models\Surprise;
use Illuminate\Http\Request;

class GeniusController extends Controller
{
    public function dashboard($geniusId)
{
    $surprises = Surprise::with([
        'skill:id,name',
    ])
        ->where('genius_id', $geniusId)
        ->where('status', 'completed')
        ->orderByDesc('completed_at')
        ->limit(10)
        ->get([
            'id',
            'title',
            'description',
            'size',
            'skill_id',
            'created_at',
            'started_at',
            'delivered_at',
            'completed_at',
            'hours_spent',
            'final_price',
            'target_city',
            'target_province',
            'target_country',
        ]);

    return response()->json([
        'success' => true,
        'data' => $surprises,
    ]);
}
public function feed(Request $request)
{
    $genius = $request->user();

    if (!$genius) {
        return response()->json([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ], 401);
    }

    // Skills activas del genio
    $activeSkillIds = $genius->activeSkills()
        ->pluck('skills.id');

    // Skills propuestas todavía no activadas
    $proposedSkillIds = $genius->proposedSkills()
        ->pluck('skills.id');

    // Unificar sin duplicados
    $skillIds = $activeSkillIds
        ->merge($proposedSkillIds)
        ->unique()
        ->values()
        ->toArray();

    $now = now();

    $surprises = Surprise::with([
        'skill:id,name',
        'creator:id,name,avatar',
        'ads' => function ($q) {
            $q->where('is_active', 1)
                ->orderBy('priority', 'desc');
        }
    ])
        ->where('status', 'open')
        ->where('creator_id', '!=', $genius->id)
        ->whereIn('skill_id', $skillIds)
        ->get()
        ->filter(function ($surprise) use ($now) {

            // Sin anuncio: visible como sorpresa normal
            if ($surprise->ads->isEmpty()) {
                return true;
            }

            $ad = $surprise->ads->first();

            // Anuncio expirado
            if ($ad->expires_at && $ad->expires_at->isPast()) {
                return false;
            }

            // Acceso anticipado
            $earlyAccessHours = $ad->early_access_hours ?? 0;

            if (
                $earlyAccessHours > 0 &&
                $ad->activated_at
            ) {
                $visibleAt = $ad->activated_at
                    ->copy()
                    ->addHours($earlyAccessHours);

                if ($now->lt($visibleAt)) {
                    return false;
                }
            }

            return true;
        })
        ->sortByDesc(function ($surprise) {
            $ad = $surprise->ads->first();

            return [
                $surprise->is_urgent ? 1 : 0,
                $ad->priority ?? 0,
                optional($surprise->created_at)->timestamp ?? 0,
            ];
        })
        ->values()
        ->map(function ($surprise) {
            $data = $surprise->toArray();

            $data['location'] = [
                'city' => $surprise->target_city,
                'province' => $surprise->target_province,
                'country' => $surprise->target_country,
            ];

            $data['xp_value'] = $surprise->xp_value ?? 0;

            return $data;
        })
        ->values();

    return response()->json([
        'success' => true,
        'data' => $surprises
    ]);
}
public function history(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'error' => 'Usuario no autenticado'
        ], 401);
    }

    $surprises = Surprise::with([
        'skill:id,name',
        'creator:id,name,avatar',
    ])
        ->where('genius_id', $user->id)
        ->whereIn('status', [
            'completed',
            'cancelled',
            'canceled',
        ])
        ->orderByDesc('updated_at')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $surprises,
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
