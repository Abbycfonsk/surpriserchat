<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TopSkillController extends Controller
{
    // GET /skills/{skillId}/top
    public function topBySkill($skillId)
    {
        $skill = Skill::findOrFail($skillId);

        // Obtener top 10 genios con esa skill activa
        $top = DB::table('user_skills')
            ->join('users', 'users.id', '=', 'user_skills.user_id')
            ->leftJoin('surprises', function ($join) use ($skillId) {
                $join->on('surprises.genius_id', '=', 'users.id')
                    ->where('surprises.skill_id', '=', $skillId)
                    ->where('surprises.status', '=', 'completed');
            })
            ->select(
                'users.id as user_id',
                'users.name',
                'user_skills.level',
                'user_skills.xp',
                DB::raw('COUNT(surprises.id) as completed_surprises')
            )
            ->where('user_skills.skill_id', $skillId)
            ->groupBy('users.id', 'users.name', 'user_skills.level', 'user_skills.xp')
            ->orderByDesc('user_skills.level')
            ->orderByDesc('user_skills.xp')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'skill' => $skill->name,
            'top10' => $top
        ]);
    }
}
