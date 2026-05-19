<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreatorPackage;

class CreatorPackageController extends Controller
{
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'package_type' => 'required|in:1,3,5'
        ]);

        $user = $request->user();

        if ($user->is_creator !== 1) {
            return response()->json([
                'error' => 'Only creators can purchase packages'
            ], 403);
        }

        // Crear paquete
        $package = CreatorPackage::create([
            'user_id' => $user->id,
            'ads_total' => $validated['package_type'],
            'ads_used' => 0,
            'is_active' => 1
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Package purchased successfully',
            'data' => $package
        ]);
    }
    public function current(Request $request)
{
    $user = $request->user();

    $package = CreatorPackage::where('user_id', $user->id)
        ->where('is_active', 1)
        ->orderBy('id', 'desc')
        ->first();

    if (!$package) {
        return response()->json([
            'success' => true,
            'message' => 'No active package',
            'data' => null
        ]);
    }

    return response()->json([
        'success' => true,
        'data' => $package
    ]);
}

public function history(Request $request)
{
    $user = $request->user();

    $packages = CreatorPackage::where('user_id', $user->id)
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $packages
    ]);
}
}