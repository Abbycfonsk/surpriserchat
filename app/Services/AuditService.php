<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log($action, $model = null, $modelId = null, $old = null, $new = null)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => $old,
            'new_values' => $new,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
