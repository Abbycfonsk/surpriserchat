<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSkill;
use App\Models\Review;
use App\Models\Surprise;

class GeniusController extends Controller
{
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
