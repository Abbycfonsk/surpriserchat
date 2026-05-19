<?php
// app/Http/Middleware/CheckSuspended.php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserSuspension;
use Carbon\Carbon;

class CheckSuspended
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user->is_genius) {
            $suspension = UserSuspension::where('user_id', $user->id)->first();

            if ($suspension && Carbon::now()->lt($suspension->suspended_until)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tu cuenta de genio está suspendida hasta ' . $suspension->suspended_until,
                ], 403);
            }
        }

        return $next($request);
    }
}
