<?php
// app/Services/PenaltyService.php

namespace App\Services;

use App\Models\Penalty;
use App\Models\UserSuspension;
use Carbon\Carbon;

class PenaltyService
{
    public static function applyCancellationPenalty($geniusId, $reasonType, $reasonKey)
    {
        if ($reasonType === 'valid') {
            return; // no penalización
        }

        // contar penalizaciones recientes
        $last60 = Penalty::where('user_id', $geniusId)
            ->whereIn('type', ['cancellation_doubtful', 'cancellation_invalid'])
            ->where('created_at', '>=', Carbon::now()->subDays(60))
            ->count();

        // determinar suspensión
        $suspensionHours = null;

        if ($reasonType === 'doubtful') {
            if ($last60 >= 2 && $last60 < 3) $suspensionHours = 48;
            if ($last60 >= 3 && $last60 < 5) $suspensionHours = 48;
            if ($last60 >= 5) $suspensionHours = 168; // 7 días
        }

        if ($reasonType === 'invalid') {
            if ($last60 >= 1 && $last60 < 2) $suspensionHours = 48;
            if ($last60 >= 2 && $last60 < 5) $suspensionHours = 168; // 7 días
            if ($last60 >= 5) $suspensionHours = 720; // 30 días
        }

        // registrar penalización
        Penalty::create([
            'user_id' => $geniusId,
            'type' => $reasonType === 'doubtful' ? 'cancellation_doubtful' : 'cancellation_invalid',
            'reason_key' => $reasonKey,
            'starts_at' => Carbon::now(),
            'ends_at' => $suspensionHours ? Carbon::now()->addHours($suspensionHours) : null,
        ]);

        // aplicar suspensión si corresponde
        if ($suspensionHours) {
            UserSuspension::updateOrCreate(
                ['user_id' => $geniusId],
                [
                    'suspended_until' => Carbon::now()->addHours($suspensionHours),
                    'reason' => "Suspensión por cancelaciones ($reasonType)"
                ]
            );
        }
    }
}
