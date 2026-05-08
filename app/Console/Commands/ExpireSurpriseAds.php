<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SurpriseAd;

class ExpireSurpriseAds extends Command
{
    protected $signature = 'surprise_ads:expire';
    protected $description = 'Deactivate expired surprise ads';

    public function handle()
    {
        $expiredAds = SurpriseAd::where('is_active', 1)
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredAds as $ad) {
            $ad->is_active = 0;
            $ad->save();
            $count++;
        }

        $this->info("Expired ads deactivated: {$count}");

        return Command::SUCCESS;
    }
}