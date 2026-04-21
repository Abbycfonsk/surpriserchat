<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Surprise;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    // ============================
    //  ABRIR DISPUTA
    // ============================

    // POST /surprises/{id}/dispute
    public function openDispute(Request $request, $id)
    {
        $user = $request->user();
        $surprise = Surprise::findOrFail($id);

        if (!in_array($user->id, [$surprise->creator_id, $surprise->genius_id])) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        if (!in_array($surprise->status, ['delivered', 'completed'])) {
            return response()->json(['error' => 'Dispute not allowed in this status'], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        if (Dispute::where('surprise_id', $id)->where('status', 'open')->exists()) {
            return response()->json(['error' => 'Dispute already open'], 400);
        }

        $dispute = Dispute::create([
            'surprise_id' => $surprise->id,
            'creator_id' => $surprise->creator_id,
            'genius_id' => $surprise->genius_id,
            'opened_by' => $user->id,
            'reason' => $request->reason,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dispute opened',
            'data' => $dispute
        ]);
    }

    // ============================
    //  VER UNA DISPUTA
    // ============================

    // GET /disputes/{id}
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $dispute = Dispute::with(['surprise', 'creator', 'genius'])
            ->findOrFail($id);

        if (!in_array($user->id, [
            $dispute->creator_id,
            $dispute->genius_id,
            // el admin la verá por AdminController, no por aquí
        ])) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $dispute
        ]);
    }

    // ============================
    //  LISTAR DISPUTAS DEL USUARIO
    // ============================

    // GET /my/disputes
    public function myDisputes(Request $request)
    {
        $user = $request->user();

        $disputes = Dispute::with('surprise')
            ->where(function ($q) use ($user) {
                $q->where('creator_id', $user->id)
                    ->orWhere('genius_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $disputes
        ]);
    }

    // ============================
    //  LISTAR DISPUTAS COMO CREATOR
    // ============================

    // GET /users/{id}/disputes/creator
    public function disputesAsCreator($userId)
    {
        $disputes = Dispute::with(['surprise', 'genius'])
            ->where('creator_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $disputes
        ]);
    }

    // ============================
    //  LISTAR DISPUTAS COMO GENIUS
    // ============================

    // GET /users/{id}/disputes/genius
    public function disputesAsGenius($userId)
    {
        $disputes = Dispute::with(['surprise', 'creator'])
            ->where('genius_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $disputes
        ]);
    }
}
