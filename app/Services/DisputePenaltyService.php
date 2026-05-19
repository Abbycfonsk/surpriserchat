<?php

namespace App\Services;

use App\Models\DisputePenalty;
use App\Models\UserSuspension;
use App\Models\Dispute;
use Carbon\Carbon;


class DisputePenaltyService
{
    public static function applyPenalty(Dispute $dispute)
    {
        $geniusId = $dispute->genius_id;

        // contar disputas perdidas recientes
        $lost60 = Dispute::where('genius_id', $geniusId)
            ->where('winner', 'creator')
            ->where('resolved_at', '>=', Carbon::now()->subDays(60))
            ->count();

        $lost90 = Dispute::where('genius_id', $geniusId)
            ->where('winner', 'creator')
            ->where('resolved_at', '>=', Carbon::now()->subDays(90))
            ->count();

        // contar cuántas veces llegó a 8
        $timesReached8 = DisputePenalty::where('genius_id', $geniusId)
            ->where('penalty_level', 'suspension_30d')
            ->count();

        $penaltyLevel = 'warning';
        $suspensionHours = null;

        // reglas
        if ($lost60 >= 5 && $lost60 < 8) {
            $penaltyLevel = 'suspension_7d';
            $suspensionHours = 168;
        }

        if ($lost90 >= 8) {
            if ($timesReached8 >= 1) {
                $penaltyLevel = 'ban_permanent';
            } else {
                $penaltyLevel = 'suspension_30d';
                $suspensionHours = 720;
            }
        }

        // registrar penalización
        DisputePenalty::create([
            'dispute_id' => $dispute->id,
            'genius_id' => $geniusId,
            'penalty_level' => $penaltyLevel,
            'starts_at' => Carbon::now(),
            'ends_at' => $suspensionHours ? Carbon::now()->addHours($suspensionHours) : null,
        ]);

        // aplicar suspensión
        if ($penaltyLevel === 'suspension_7d' || $penaltyLevel === 'suspension_30d') {
            UserSuspension::updateOrCreate(
                ['user_id' => $geniusId],
                [
                    'suspended_until' => Carbon::now()->addHours($suspensionHours),
                    'reason' => "Suspensión por disputas perdidas"
                ]
            );
        }

        // ban permanente
        if ($penaltyLevel === 'ban_permanent') {
            $user = \App\Models\User::find($geniusId);
            $user->banned = 1;
            $user->save();
        }
    }
}
