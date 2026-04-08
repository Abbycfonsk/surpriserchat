<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    // Listar todas las skills
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Skill::all()
        ]);
    }

    // Asignar skill a usuario
    public function assignSkill(Request $request, $userId)
    {
        $request->validate([
            'skill_id' => 'required|exists:skills,id'
        ]);

        $user = User::findOrFail($userId);

        // Evitar duplicados
        if ($user->skills()->where('skill_id', $request->skill_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Skill already assigned'
            ], 400);
        }

        $user->skills()->attach($request->skill_id);

        return response()->json([
            'success' => true,
            'message' => 'Skill assigned successfully'
        ]);
    }

    // Quitar skill
    public function removeSkill($userId, $skillId)
    {
        $user = User::findOrFail($userId);

        $user->skills()->detach($skillId);

        return response()->json([
            'success' => true,
            'message' => 'Skill removed successfully'
        ]);
    }

    // Ver skills del usuario con nivel automático
    public function userSkills($userId)
    {
        $user = User::findOrFail($userId);

        return response()->json([
            'success' => true,
            'data' => $user->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'category' => $skill->category,
                    'level' => $skill->pivot->level, // ahora sí funciona
                ];
            })
        ]);
    }
}
