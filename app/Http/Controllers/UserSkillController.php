<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use App\Models\Surprise;

class UserSkillController extends Controller
{

    public function allProgress($userId)
    {
        $user = User::findOrFail($userId);

        $skills = $user->skills()->get();

        $result = [];

        foreach ($skills as $skill) {

            $completed = Surprise::where('genius_id', $userId)
                ->where('skill_id', $skill->id)
                ->count();

            // Nivel
            if ($completed >= 50) {
                $level = 5;
                $next = null;
            } elseif ($completed >= 25) {
                $level = 4;
                $next = 50;
            } elseif ($completed >= 10) {
                $level = 3;
                $next = 25;
            } elseif ($completed >= 4) {
                $level = 2;
                $next = 10;
            } else {
                $level = 1;
                $next = 4;
            }

            $remaining = $next ? $next - $completed : 0;
            $progress = $next ? round(($completed / $next) * 100, 1) : 100;

            $result[] = [
                'skill_id' => $skill->id,
                'skill_name' => $skill->name,
                'level' => $level,
                'completed' => $completed,
                'next_level_at' => $next,
                'remaining' => $remaining,
                'progress_percentage' => $progress
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    public function ranking($skillId)
    {
        $skill = Skill::findOrFail($skillId);

        // Contar sorpresas por genius
        $geniusData = Surprise::where('skill_id', $skillId)
            ->whereNotNull('genius_id')
            ->selectRaw('genius_id, COUNT(*) as total')
            ->groupBy('genius_id')
            ->orderByDesc('total')
            ->get();

        $ranking = [];

        foreach ($geniusData as $index => $row) {

            $user = User::find($row->genius_id);

            // Calcular nivel
            $completed = $row->total;

            if ($completed >= 50) $level = 5;
            elseif ($completed >= 25) $level = 4;
            elseif ($completed >= 10) $level = 3;
            elseif ($completed >= 4) $level = 2;
            else $level = 1;

            $ranking[] = [
                'position' => $index + 1,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'completed' => $completed,
                'level' => $level
            ];
        }

        return response()->json([
            'success' => true,
            'skill' => $skill->name,
            'ranking' => $ranking
        ]);
    }
    public function history($userId, $skillId)
    {
        $history = Surprise::where('genius_id', $userId)
            ->where('skill_id', $skillId)
            ->with(['creator', 'files', 'reviews'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
    public function globalLevel($userId)
    {
        $user = User::findOrFail($userId);

        $skills = $user->skills()->get();

        if ($skills->isEmpty()) {
            return response()->json([
                'success' => true,
                'global_level' => 1
            ]);
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($skills as $skill) {

            $completed = Surprise::where('genius_id', $userId)
                ->where('skill_id', $skill->id)
                ->count();

            // Nivel
            if ($completed >= 50) $level = 5;
            elseif ($completed >= 25) $level = 4;
            elseif ($completed >= 10) $level = 3;
            elseif ($completed >= 4) $level = 2;
            else $level = 1;

            $weightedSum += $level * $completed;
            $totalWeight += $completed;
        }

        $global = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 1;

        return response()->json([
            'success' => true,
            'global_level' => $global
        ]);
    }
    public function dashboard($userId)
    {
        return response()->json([
            'success' => true,
            'progress' => $this->allProgress($userId)->original['data'],
            'global_level' => $this->globalLevel($userId)->original['global_level']
        ]);
    }
    public function progress($userId, $skillId)
    {
        $user = User::findOrFail($userId);
        $skill = Skill::findOrFail($skillId);

        // Contar sorpresas realizadas como GENIUS en ese skill
        $completed = Surprise::where('genius_id', $userId)
            ->where('skill_id', $skillId)
            ->count();

        // Determinar nivel actual
        if ($completed >= 50) {
            $level = 5;
            $next = null;
        } elseif ($completed >= 25) {
            $level = 4;
            $next = 50;
        } elseif ($completed >= 10) {
            $level = 3;
            $next = 25;
        } elseif ($completed >= 4) {
            $level = 2;
            $next = 10;
        } else {
            $level = 1;
            $next = 4;
        }

        // Calcular faltantes
        $remaining = $next ? $next - $completed : 0;

        // Porcentaje de progreso
        $progress = $next
            ? round(($completed / $next) * 100, 1)
            : 100;

        return response()->json([
            'success' => true,
            'data' => [
                'skill_id' => $skill->id,
                'skill_name' => $skill->name,
                'level' => $level,
                'completed' => $completed,
                'next_level_at' => $next,
                'remaining' => $remaining,
                'progress_percentage' => $progress
            ]
        ]);
    }
}
