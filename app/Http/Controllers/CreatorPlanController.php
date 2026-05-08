<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreatorPlan;
use App\Models\User;
use Carbon\Carbon;

class CreatorPlanController extends Controller
{
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'plan_type' => 'required|in:starter,pro,premium'
        ]);

        $user = $request->user();

        // 1) Verificar que el usuario es creador
        if ($user->role !== 'creator') {
            return response()->json([
                'error' => 'Only creators can purchase plans'
            ], 403);
        }

        // 2) Buscar plan activo
        $activePlan = CreatorPlan::where('user_id', $user->id)
            ->where('is_active', 1)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        // 3) Si ya tiene un plan activo del mismo tipo → error
        if ($activePlan && $activePlan->plan_type === $validated['plan_type']) {
            return response()->json([
                'error' => 'You already have this plan active'
            ], 400);
        }

        // 4) Si tiene un plan activo y quiere cambiar → upgrade inmediato
        if ($activePlan && $activePlan->plan_type !== $validated['plan_type']) {
            $activePlan->is_active = 0;
            $activePlan->save();
        }

        // 5) Configuración de anuncios por plan
        $adsByPlan = [
            'starter' => 5,
            'pro' => 12,
            'premium' => 25,
        ];

        $adsTotal = $adsByPlan[$validated['plan_type']];

        // 6) Crear nuevo plan
        $plan = CreatorPlan::create([
            'user_id' => $user->id,
            'plan_type' => $validated['plan_type'],
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'ads_total' => $adsTotal,
            'ads_used' => 0,
            'is_active' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Plan purchased successfully',
            'data' => $plan
        ]);
    }

    public function current(Request $request)
    {
        $user = $request->user();

        $plan = CreatorPlan::where('user_id', $user->id)
            ->where('is_active', 1)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }
}