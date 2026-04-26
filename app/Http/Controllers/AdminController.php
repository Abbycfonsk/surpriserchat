<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Surprise;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationEvents;

class AdminController extends Controller
{
    // ============================
    //  DASHBOARD ADMIN
    // ============================

    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => User::count(),
                'total_genius' => User::where('is_genius', 1)->count(),
                'total_creator' => User::where('is_creator', 1)->count(),
                'total_surprises' => Surprise::count(),
                'pending_disputes' => Dispute::where('status', 'open')->count(),
                'completed_surprises' => Surprise::where('status', 'completed')->count(),
                'cancelled_surprises' => Surprise::where('status', 'cancelled')->count(),
            ]
        ]);
    }

    // ============================
    //  DISPUTAS
    // ============================
    public function listDisputes(Request $request)
    {
        $query = Dispute::with(['surprise', 'creator', 'genius']);

        // ============================
        // FILTROS OPCIONALES
        // ============================

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('winner')) {
            $query->where('winner', $request->winner);
        }

        if ($request->has('creator_id')) {
            $query->where('creator_id', $request->creator_id);
        }

        if ($request->has('genius_id')) {
            $query->where('genius_id', $request->genius_id);
        }

        if ($request->has('surprise_id')) {
            $query->where('surprise_id', $request->surprise_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'LIKE', "%$search%")
                    ->orWhere('resolution', 'LIKE', "%$search%");
            });
        }

        // ============================
        // ORDEN Y PAGINACIÓN
        // ============================

        $query->orderBy('created_at', 'desc');

        $disputes = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $disputes
        ]);
    }

    public function resolveDispute(Request $request, $id)
    {
        $admin = $request->user();

        if (!$admin->is_admin) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $dispute = \App\Models\Dispute::with(['surprise', 'creator', 'genius'])->findOrFail($id);

        if ($dispute->status !== 'open') {
            return response()->json(['error' => 'Dispute already resolved'], 400);
        }

        $request->validate([
            'winner' => 'required|in:creator,genius,none',
            'resolution' => 'required|string|max:1000'
        ]);

        // Actualizar disputa
        $dispute->winner = $request->winner;
        $dispute->resolution = $request->resolution;
        $dispute->status = 'resolved';
        $dispute->resolved_at = now();
        $dispute->save();

        // Penalización automática si pierde el genio
        if ($dispute->winner === 'creator') {
            \App\Services\DisputePenaltyService::applyPenalty($dispute);
        }

        // Auditoría
        \App\Services\AuditService::log(
            'dispute_resolved',
            'Dispute',
            $dispute->id,
            ['status' => 'open'],
            [
                'status' => 'resolved',
                'winner' => $dispute->winner
            ]
        );

        // Notificación
        NotificationEvents::disputeResolved($dispute);

        return response()->json([
            'success' => true,
            'message' => 'Dispute resolved successfully',
            'data' => $dispute
        ]);
    }
    // ============================
    //  USUARIOS
    // ============================

    public function listUsers()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);

        $user->banned = 1;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User banned successfully'
        ]);
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);

        $user->banned = 0;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User unbanned successfully'
        ]);
    }

    // ============================
    //  SORPRESAS
    // ============================

    public function listSurprises()
    {
        $surprises = Surprise::with(['creator', 'genius'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $surprises
        ]);
    }

    public function forceCancel($id)
    {
        $surprise = Surprise::findOrFail($id);

        if ($surprise->status === 'cancelled') {
            return response()->json(['error' => 'Already cancelled'], 400);
        }

        $surprise->status = 'cancelled';
        $surprise->cancelled_by_admin = 1;
        $surprise->save();

        return response()->json([
            'success' => true,
            'message' => 'Surprise cancelled by admin'
        ]);
    }
}
