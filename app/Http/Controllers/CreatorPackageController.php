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

        if ($user->role !== 'creator') {
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
}