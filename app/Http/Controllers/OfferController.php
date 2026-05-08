<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferBid;
use App\Models\Surprise;
use Illuminate\Http\Request;
use App\Services\NotificationEvents;
use App\Services\SanitizerService;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    // Genius envía una oferta inicial
   public function store(Request $request, $surpriseId)
{
    $surprise = Surprise::findOrFail($surpriseId);
    $user = $request->user();

    // 1) Solo si la sorpresa está OPEN
    if ($surprise->status !== 'open') {
        return response()->json([
            'error' => 'You can only send offers for open surprises'
        ], 400);
    }

    // 2) Validación básica
    $validated = $request->validate([
        'price' => 'required|numeric|min:1',
        'message' => 'nullable|string',
        'eta_hours' => 'nullable|integer|min:1'
    ]);

    // 3) Validar rango de precio según tamaño de sorpresa
    $range = config('surprises.price_ranges.' . $surprise->size) ?? null;

    if ($range) {
        if ($validated['price'] < $range['min'] || $validated['price'] > $range['max']) {
            return response()->json([
                'error' => 'Price is not realistic for this surprise type'
            ], 422);
        }
    }

    // 4) Evitar más de 1 oferta activa por genius + surprise
    $existingOffer = Offer::where('surprise_id', $surprise->id)
        ->where('genius_id', $user->id)
        ->whereIn('status', ['pending', 'negotiating'])
        ->first();

    if ($existingOffer) {
        return response()->json([
            'error' => 'You already have an active offer for this surprise'
        ], 400);
    }

    /* ============================================
       4.5) NUEVO: Límite diario de ofertas
       ============================================ */
    $todayOffers = Offer::where('genius_id', $user->id)
        ->whereDate('created_at', today())
        ->count();

    $dailyLimit = $user->dailyOfferLimit();

    if ($todayOffers >= $dailyLimit) {
        return response()->json([
            'error' => 'You have reached your daily offer limit'
        ], 429);
    }

    // 5) Cooldown: no más de 1 oferta cada 30s
    $lastOffer = Offer::where('genius_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->first();

    if ($lastOffer && $lastOffer->created_at->gt(now()->subSeconds(30))) {
        return response()->json([
            'error' => 'You are sending offers too fast. Please wait a bit.'
        ], 429);
    }

    // 6) Evitar duplicados recientes (mismo price + eta + message)
    $duplicate = Offer::where('genius_id', $user->id)
        ->where('surprise_id', $surprise->id)
        ->where('price', $validated['price'])
        ->where('eta_hours', $validated['eta_hours'] ?? null)
        ->where('message', $validated['message'] ?? null)
        ->where('created_at', '>=', now()->subMinutes(5))
        ->exists();

    if ($duplicate) {
        return response()->json([
            'error' => 'You already sent an identical offer recently'
        ], 400);
    }

    // 7) Sanitizar mensaje
    if (isset($validated['message'])) {
        $validated['message'] = SanitizerService::clean($validated['message']);
    }

    // 8) Crear oferta base
    $offer = Offer::create([
        'surprise_id' => $surprise->id,
        'genius_id' => $user->id,
        'price' => $validated['price'],
        'original_price' => $validated['price'],
        'message' => $validated['message'] ?? null,
        'eta_hours' => $validated['eta_hours'] ?? null,
        'status' => 'pending',
        'creator_bid_count' => 0,
        'genius_bid_count' => 0,
    ]);

    // 9) Auditoría
    AuditService::log(
        'offer_created',
        'Offer',
        $offer->id,
        null,
        $offer->toArray()
    );

    // 10) Notificación al creador
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
            ->with(['genius', 'bids'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $offers
        ]);
    }

    // Regateo: creador o genius envían contraoferta
    public function counterOffer(Request $request, $offerId)
    {
        $offer = Offer::with('surprise')->findOrFail($offerId);
        $user = $request->user();
        $surprise = $offer->surprise;

        $validated = $request->validate([
            'price' => 'required|numeric|min:1',
            'eta_hours' => 'nullable|integer|min:1',
            'message' => 'nullable|string',
        ]);

        // 1) Solo creador o genius de esa oferta
        if (!in_array($user->id, [$offer->genius_id, $surprise->creator_id])) {
            return response()->json(['error' => 'Not authorized for this negotiation'], 403);
        }

        // 2) Determinar rol
        $role = $user->id === $offer->genius_id ? 'genius' : 'creator';

        // 3) Limitar número de bids por rol (máx 5)
        if ($role === 'creator' && $offer->creator_bid_count >= 5) {
            return response()->json([
                'error' => 'Creator has reached the maximum number of counteroffers for this offer'
            ], 400);
        }

        if ($role === 'genius' && $offer->genius_bid_count >= 5) {
            return response()->json([
                'error' => 'Genius has reached the maximum number of counteroffers for this offer'
            ], 400);
        }

        // 4) Limitar total de bids (máx 10)
        $totalBids = $offer->bids()->count();
        if ($totalBids >= 10) {
            return response()->json([
                'error' => 'This negotiation has reached the maximum number of rounds'
            ], 400);
        }

        // 5) Cooldown entre bids (ej. 5s)
        $lastBid = $offer->bids()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastBid && $lastBid->created_at->gt(now()->subSeconds(5))) {
            return response()->json([
                'error' => 'You are sending counteroffers too fast'
            ], 429);
        }

        // 6) Validar rango de precio según tamaño de sorpresa
        $range = config('surprises.price_ranges.' . $surprise->size) ?? null;

        if ($range) {
            if ($validated['price'] < $range['min'] || $validated['price'] > $range['max']) {
                return response()->json([
                    'error' => 'Price is not realistic for this surprise type'
                ], 422);
            }
        }

        // 7) Sanitizar mensaje
        if (isset($validated['message'])) {
            $validated['message'] = SanitizerService::clean($validated['message']);
        }

        // 8) Crear bid
        $bid = OfferBid::create([
            'offer_id' => $offer->id,
            'user_id' => $user->id,
            'role' => $role,
            'price' => $validated['price'],
            'eta_hours' => $validated['eta_hours'] ?? $offer->eta_hours,
            'message' => $validated['message'] ?? null,
        ]);

        // 9) Actualizar oferta con el último precio/ETA/mensaje
        $old = $offer->getOriginal();

        $updateData = [
            'price' => $bid->price,
            'eta_hours' => $bid->eta_hours,
            'message' => $bid->message,
            'status' => 'negotiating',
        ];

        if ($role === 'creator') {
            $updateData['creator_bid_count'] = $offer->creator_bid_count + 1;
        } else {
            $updateData['genius_bid_count'] = $offer->genius_bid_count + 1;
        }

        $offer->update($updateData);

        // 10) Auditoría
        AuditService::log(
            'offer_countered',
            'Offer',
            $offer->id,
            $old,
            $offer->getChanges()
        );

        // 11) Notificación al otro lado
        NotificationEvents::offerCountered($offer, $bid);

        return response()->json([
            'success' => true,
            'message' => 'Counteroffer sent successfully',
            'data' => [
                'offer' => $offer,
                'bid' => $bid,
            ]
        ]);
    }

    // Creador acepta una oferta
    public function accept($offerId)
    {
        $offer = Offer::with('surprise')->findOrFail($offerId);
        $surprise = $offer->surprise;

        // Solo el creador puede aceptar
        if (Auth::id() !== $surprise->creator_id) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        // Marcar oferta como aceptada
        $offer->status = 'accepted';
        $offer->save();

        // Asignar genius a la sorpresa
        $surprise->genius_id = $offer->genius_id;
        $surprise->status = 'in_progress';
        $surprise->price_genius = $offer->original_price; // precio inicial del genius
        $surprise->final_price = $offer->price; // precio final tras regateo
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

        // Auditoría
        AuditService::log(
            'offer_accepted',
            'Offer',
            $offer->id,
            null,
            $offer->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Offer accepted and genius assigned',
            'data' => $offer
        ]);
    }
}
