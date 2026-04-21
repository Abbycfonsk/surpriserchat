<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Surprise;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function listDisputes()
    {
        $disputes = Dispute::with(['surprise', 'creator', 'genius'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $disputes
        ]);
    }

    public function resolveDispute(Request $request, $id)
    {
        $request->validate([
            'resolution' => 'required|string|max:500',
            'winner' => 'required|in:creator,genius,none'
        ]);

        $dispute = Dispute::findOrFail($id);

        if ($dispute->status !== 'open') {
            return response()->json(['error' => 'Dispute already resolved'], 400);
        }

        $dispute->status = 'resolved';
        $dispute->resolution = $request->resolution;
        $dispute->winner = $request->winner;
        $dispute->resolved_at = now();
        $dispute->save();

        return response()->json([
            'success' => true,
            'message' => 'Dispute resolved successfully'
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
