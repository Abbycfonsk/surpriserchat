<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Surprise;
use Illuminate\Http\Request;
use App\Helpers\Notify;
use App\Services\NotificationEvents;

class OfferController extends Controller
{
    // Genius envía una oferta
    public function store(Request $request, $surpriseId)
    {
        $surprise = Surprise::findOrFail($surpriseId);

        $validated = $request->validate([
            'genius_id' => 'required|exists:users,id',
            'price' => 'required|numeric|min:1',
            'message' => 'nullable|string',
            'eta_hours' => 'nullable|integer|min:1'
        ]);

        // Crear oferta
        $offer = Offer::create([
            'surprise_id' => $surprise->id,
            'genius_id' => $validated['genius_id'],
            'price' => $validated['price'],
            'message' => $validated['message'] ?? null,
            'eta_hours' => $validated['eta_hours'] ?? null,
        ]);

        // Notificación al creador
        NotificationEvents::offerReceived($offer);

        return response()->json([
            'success' => true,
            'message' => 'Offer sent successfully',
            'data' => $offer
        ]);
    }

    // Listar ofertas de una sorpresa
    public function listBySurprise($surpriseId)
    {
        $offers = Offer::where('surprise_id', $surpriseId)
            ->with('genius')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offers
        ]);
    }

    // Creador acepta una oferta
    public function accept($offerId)
    {
        $offer = Offer::with('surprise')->findOrFail($offerId);
        $surprise = $offer->surprise;

        // Marcar oferta como aceptada
        $offer->status = 'accepted';
        $offer->save();

        // Asignar genius a la sorpresa
        $surprise->genius_id = $offer->genius_id;
        $surprise->status = 'in_progress';
        $surprise->save();

        // Rechazar automáticamente las demás ofertas
        Offer::where('surprise_id', $surprise->id)
            ->where('id', '!=', $offer->id)
            ->update(['status' => 'rejected']);

        // Notificación al genius ganador
        NotificationEvents::offerAccepted($offer);

        // Notificación a los demás genius
        $otherOffers = Offer::where('surprise_id', $surprise->id)
            ->where('status', 'rejected')
            ->get();

        foreach ($otherOffers as $o) {
            NotificationEvents::offerRejected($o);
        }

        return response()->json([
            'success' => true,
            'message' => 'Offer accepted and genius assigned',
            'data' => $offer
        ]);
    }
}
