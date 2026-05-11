<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surprise;
use App\Models\SurpriseAd;
use App\Models\CreatorPlan;
use App\Models\CreatorPackage;
use Illuminate\Support\Facades\DB;

class SurpriseAdController extends Controller
{
    public function store(Request $request)
{
    $validated = $request->validate([
        'surprise_id' => 'required|exists:surprises,id',
    ]);

    $surprise = Surprise::findOrFail($validated['surprise_id']);
    $creator = $surprise->creator;

    // Evitar doble activación accidental
    if ($surprise->ads()->where('is_active', 1)->exists()) {
        return response()->json([
            'error' => 'This surprise already has an active ad'
        ], 400);
    }

    // Buscar plan activo
    $plan = CreatorPlan::where('user_id', $creator->id)
        ->where('is_active', 1)
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>=', now())
        ->first();

    // Buscar paquete activo
    $package = CreatorPackage::where('user_id', $creator->id)
        ->where('is_active', 1)
        ->first();

    $adType = null;
    $priority = null;
    $days = null;
    $earlyAccess = $plan ? $plan->earlyAccessHours() : 0;

    // Determinar fuente del anuncio (plan o paquete)
    if ($plan && $plan->remainingAds() > 0) {
        $days = $plan->adDurationDays();
        $priority = $plan->adPriority();
        $adType = $plan->plan_type;
    }
    elseif ($package && $package->remainingAds() > 0) {
        $days = 30;
        $priority = 1;
        $adType = 'package';
    }
    else {
        return response()->json([
            'error' => 'You have no ads available'
        ], 403);
    }

    // Transacción para evitar inconsistencias
    return DB::transaction(function () use ($surprise, $creator, $adType, $priority, $earlyAccess, $days, $plan, $package) {

        // Crear anuncio primero
        $ad = SurpriseAd::create([
            'surprise_id' => $surprise->id,
            'creator_id' => $creator->id,
            'ad_type' => $adType,
            'priority' => $priority,
            'early_access_hours' => $earlyAccess,
            'activated_at' => now(),
            'expires_at' => now()->addDays($days),
            'is_active' => 1
        ]);

        // Consumir anuncio DESPUÉS de crear el ad
        if ($adType !== 'package') {
            $plan->consumeAd();
        } else {
            $package->consumeAd();
        }

        return response()->json([
            'success' => true,
            'message' => 'Ad activated successfully',
            'data' => $ad
        ]);
    });
}
    public function active(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'creator') {
        return response()->json([
            'error' => 'Only creators can view their ads'
        ], 403);
    }

    $ads = SurpriseAd::where('creator_id', $user->id)
        ->where('is_active', 1)
        ->with('surprise')
        ->orderBy('priority', 'desc')
        ->orderBy('activated_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $ads
    ]);
}
public function available(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'creator') {
        return response()->json([
            'error' => 'Only creators can view available ads'
        ], 403);
    }

    // Plan activo
    $plan = CreatorPlan::where('user_id', $user->id)
        ->where('is_active', 1)
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>=', now())
        ->first();

    $planAvailable = $plan ? $plan->remainingAds() : 0;

    // Paquete activo
    $package = CreatorPackage::where('user_id', $user->id)
        ->where('is_active', 1)
        ->first();

    $packageAvailable = $package ? $package->remainingAds() : 0;

    return response()->json([
        'success' => true,
        'data' => [
            'plan_type' => $plan->plan_type ?? null,
            'plan_available' => $planAvailable,
            'package_available' => $packageAvailable,
            'total_available' => $planAvailable + $packageAvailable
        ]
    ]);
}
public function history(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'creator') {
        return response()->json([
            'error' => 'Only creators can view their ad history'
        ], 403);
    }

    // Obtener todos los anuncios del creador
    $ads = SurpriseAd::where('creator_id', $user->id)
        ->with('surprise')
        ->orderBy('activated_at', 'desc')
        ->get();

    // Agrupar
    $active = $ads->where('is_active', 1)->values();
    $expired = $ads->where('is_active', 0)->values();

    return response()->json([
        'success' => true,
        'data' => [
            'active' => $active,
            'expired' => $expired,
            'total' => $ads->count()
        ]
    ]);
}
public function reactivate(Request $request, $id)
{
    $user = $request->user();

    if ($user->role !== 'creator') {
        return response()->json(['error' => 'Only creators can reactivate ads'], 403);
    }

    $ad = SurpriseAd::where('id', $id)
        ->where('creator_id', $user->id)
        ->firstOrFail();

    if ($ad->is_active == 1) {
        return response()->json(['error' => 'This ad is already active'], 400);
    }

    if ($ad->expires_at->isFuture()) {
        return response()->json(['error' => 'This ad has not expired yet'], 400);
    }

    // Buscar plan activo
    $plan = CreatorPlan::where('user_id', $user->id)
        ->where('is_active', 1)
        ->where('starts_at', '<=', now())
        ->where('ends_at', '>=', now())
        ->first();

    // Buscar paquete activo
    $package = CreatorPackage::where('user_id', $user->id)
        ->where('is_active', 1)
        ->first();

    $days = null;
    $priority = null;
    $adType = null;

    // Usar plan si hay anuncios disponibles
    if ($plan && $plan->remainingAds() > 0) {
        $days = $plan->adDurationDays();
        $priority = $plan->adPriority();
        $adType = $plan->plan_type;
        $plan->consumeAd();
    }
    // Usar paquete si no hay plan
    elseif ($package && $package->remainingAds() > 0) {
        $days = 30;
        $priority = 1;
        $adType = 'package';
        $package->consumeAd();
    }
    else {
        return response()->json(['error' => 'You have no ads available'], 403);
    }

    // Reactivar anuncio
    $ad->update([
        'is_active' => 1,
        'ad_type' => $adType,
        'priority' => $priority,
        'activated_at' => now(),
        'expires_at' => now()->addDays($days),
        'notified_at' => null
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Ad reactivated successfully',
        'data' => $ad
    ]);
}
public function cancel(Request $request, $id)
{
    $user = $request->user();

    if ($user->role !== 'creator') {
        return response()->json(['error' => 'Only creators can cancel ads'], 403);
    }

    $ad = SurpriseAd::where('id', $id)
        ->where('creator_id', $user->id)
        ->where('is_active', 1)
        ->firstOrFail();

    // Devolver anuncio al plan o paquete
    if ($ad->ad_type !== 'package') {
        // Es un plan
        $plan = CreatorPlan::where('user_id', $user->id)
            ->where('is_active', 1)
            ->first();

        if ($plan) {
            $plan->ads_used = max(0, $plan->ads_used - 1);
            $plan->save();
        }
    } else {
        // Es un paquete
        $package = CreatorPackage::where('user_id', $user->id)
            ->where('is_active', 1)
            ->first();

        if ($package) {
            $package->ads_used = max(0, $package->ads_used - 1);
            if ($package->ads_used < $package->ads_total) {
                $package->is_active = 1;
            }
            $package->save();
        }
    }

    // Cancelar anuncio
    $ad->update([
        'is_active' => 0,
        'expires_at' => now(),
        'notified_at' => null
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Ad cancelled and refunded successfully',
        'data' => $ad
    ]);
}
public function stats(Request $request)
{
    $user = $request->user();

    if ($user->role !== 'creator') {
        return response()->json(['error' => 'Only creators can view stats'], 403);
    }

    $ads = SurpriseAd::where('creator_id', $user->id)->get();

    $active = $ads->where('is_active', 1)->count();
    $expired = $ads->where('is_active', 0)->count();

    $plan = CreatorPlan::where('user_id', $user->id)
        ->where('is_active', 1)
        ->first();

    $package = CreatorPackage::where('user_id', $user->id)
        ->where('is_active', 1)
        ->first();

    return response()->json([
        'success' => true,
        'data' => [
            'active_ads' => $active,
            'expired_ads' => $expired,
            'total_ads' => $ads->count(),

            'plan_type' => $plan->plan_type ?? null,
            'plan_ads_used' => $plan->ads_used ?? 0,
            'plan_ads_available' => $plan ? $plan->remainingAds() : 0,

            'package_ads_used' => $package->ads_used ?? 0,
            'package_ads_available' => $package ? $package->remainingAds() : 0,

            'highlighted_surprises' => $ads->count(),
            'average_priority' => $ads->avg('priority'),
        ]
    ]);
}
}