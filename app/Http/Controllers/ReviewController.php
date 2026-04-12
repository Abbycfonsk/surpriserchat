<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Surprise;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // Crear review
    public function store(Request $request, $surpriseId)
    {
        $surprise = Surprise::findOrFail($surpriseId);

        // Validaciones de flujo
        if ($surprise->status !== 'completed') {
            return response()->json(['error' => 'Surprise must be completed before reviewing'], 400);
        }

        // Solo el creador puede valorar
        if ($surprise->creator_id != $request->user()->id) {
            return response()->json(['error' => 'Only the creator can review this surprise'], 403);
        }

        // Evitar doble review
        if (Review::where('surprise_id', $surpriseId)->exists()) {
            return response()->json(['error' => 'This surprise already has a review'], 400);
        }

        // Validación de datos (sin reviewer_id)
        $validated = $request->validate([
            'rating_surprise' => 'required|integer|min:1|max:5',
            'rating_genius' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        // Crear review
        $review = Review::create([
            'surprise_id' => $surpriseId,
            'reviewer_id' => $request->user()->id, // usuario autenticado
            'reviewed_user_id' => $surprise->genius_id,
            'rating_surprise' => $validated['rating_surprise'],
            'rating_genius' => $validated['rating_genius'],
            'comment' => $validated['comment'] ?? null
        ]);

        // Recalcular rating del genio
        $genius = User::find($surprise->genius_id);
        $genius->genius_avg_rating = Review::where('reviewed_user_id', $genius->id)->avg('rating_genius');

        // Recalcular nivel del genio
        $genius->genius_level = $this->calculateGeniusLevel($genius);

        // Guardar cambios
        $genius->save();

        return response()->json([
            'success' => true,
            'message' => 'Review created successfully',
            'data' => $review
        ]);
    }

    // Lógica de niveles
    private function calculateGeniusLevel(User $user)
    {
        // SULTAN
        if ($user->genius_points >= 200 && $user->genius_avg_rating >= 4.8) {
            return 'SULTAN';
        }

        // GENIE
        if ($user->genius_points >= 120 && $user->genius_avg_rating >= 4.7) {
            return 'GENIE';
        }

        // FLAME
        if ($user->genius_points >= 50 && $user->genius_avg_rating >= 4.5) {
            return 'FLAME';
        }

        // SPARK
        return 'SPARK';
    }

    // Reviews recibidas por un usuario
    public function byUser($userId)
    {
        $reviews = Review::where('reviewed_user_id', $userId)
            ->with(['reviewer', 'surprise'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    // Media de puntuación del usuario (rating_genius)
    public function rating($userId)
    {
        $avg = Review::where('reviewed_user_id', $userId)->avg('rating_genius');

        return response()->json([
            'success' => true,
            'rating' => $avg ? round($avg, 2) : 0
        ]);
    }
}
