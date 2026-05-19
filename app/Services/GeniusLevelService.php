<?php

namespace App\Services;

use App\Models\User;
use App\Models\Surprise;
use App\Models\GeniusPointEvent;
use Carbon\Carbon;

class GeniusLevelService
{
    public function addPoints(User $genius, int $points, string $type, ?Surprise $surprise = null): void
    {
        GeniusPointEvent::create([
            'genius_id' => $genius->id,
            'surprise_id' => $surprise?->id,
            'type' => $type,
            'points_delta' => $points,
        ]);

        $genius->increment('genius_points', $points);

        $this->recalculateStats($genius);
        $this->recalculateLevel($genius);
    }

    public function recalculateStats(User $genius): void
    {
        $surprises = $genius->surprisesAsGenius()
            ->where('status', 'COMPLETED')
            ->get();

        $genius->genius_total_surprises = $surprises->count();
        $genius->genius_avg_rating = $surprises->avg('rating_for_genius') ?? 0;

        $genius->genius_major_disputes = $genius->pointEvents()
            ->where('type', 'DISPUTE_LOST')
            ->count();

        $genius->genius_recent_penalties = $genius->pointEvents()
            ->where('points_delta', '<', 0)
            ->where('created_at', '>', now()->subDays(90))
            ->count();

        $last20Ids = $surprises->sortByDesc('id')->take(20)->pluck('id');

        $genius->genius_last_20_penalties = $genius->pointEvents()
            ->where('points_delta', '<', 0)
            ->whereIn('surprise_id', $last20Ids)
            ->count();

        if (!$genius->genius_first_activity_at) {
            $genius->genius_first_activity_at = now();
        }

        $genius->save();
    }
    public function applyPenalty(User $genius, int $points, string $type, ?Surprise $surprise = null)
    {
        GeniusPointEvent::create([
            'genius_id' => $genius->id,
            'surprise_id' => $surprise?->id,
            'type' => $type,
            'points_delta' => -abs($points),
        ]);

        $genius->decrement('genius_points', abs($points));

        $this->recalculateStats($genius);
        $this->recalculateLevel($genius);
    }

    public function recalculateLevel(User $genius): void
    {
        $p = $genius->genius_points;
        $t = $genius->genius_total_surprises;
        $r = $genius->genius_avg_rating;
        $md = $genius->genius_major_disputes;
        $rp = $genius->genius_recent_penalties;
        $lp = $genius->genius_last_20_penalties;
        $days = $genius->genius_first_activity_at
            ? Carbon::parse($genius->genius_first_activity_at)->diffInDays(now())
            : 0;

        $new = 'SPARK';

        if ($t >= 10 && $p >= 100 && $r >= 4.5 && $md == 0 && $rp == 0) {
            $new = 'FLAME';
        }

        if ($t >= 30 && $p >= 300 && $r >= 4.6 && $md == 0 && $rp == 0 && $days >= 90) {
            $new = 'GENIE';
        }

        if ($t >= 70 && $p >= 700 && $r >= 4.7 && $md == 0 && $lp == 0 && $genius->identity_verified) {
            $new = 'SULTAN';
        }

        $genius->genius_level = $new;
        $genius->save();
    }
}
