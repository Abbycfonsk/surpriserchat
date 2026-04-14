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

    // ============================
    //  SKILLS PROPUESTAS (GENIO)
    // ============================

    // POST /users/{id}/proposed-skills
    public function updateProposedSkills(Request $request, $userId)
    {
        $request->validate([
            'skills' => 'required|array',
            'skills.*' => 'exists:skills,id'
        ]);

        $user = User::findOrFail($userId);

        // Sincronizar skills propuestas
        $user->proposedSkills()->sync($request->skills);

        return response()->json([
            'success' => true,
            'message' => 'Proposed skills updated'
        ]);
    }

    // ============================
    //  VER SKILLS DEL GENIO
    // ============================

    // GET /users/{id}/skills
    public function userSkills($userId)
    {
        $user = User::with(['proposedSkills', 'activeSkills'])->findOrFail($userId);

        // Skills propuestas (nivel 0)
        $proposed = $user->proposedSkills->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
                'level' => 0,
                'status' => 'proposed'
            ];
        });

        // Skills activas (nivel real)
        $active = $user->activeSkills->map(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
                'level' => $skill->pivot->level,
                'xp' => $skill->pivot->xp,
                'status' => 'active'
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'proposed' => $proposed,
                'active' => $active
            ]
        ]);
    }
}
