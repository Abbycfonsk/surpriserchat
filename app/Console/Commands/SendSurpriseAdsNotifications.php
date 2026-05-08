<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SurpriseAd;
use App\Models\User;
use App\Events\NotificationEvents;

class SendSurpriseAdsNotifications extends Command
{
    protected $signature = 'surprise_ads:notify';
    protected $description = 'Send notifications for new surprise ads every 3 hours';

    public function handle()
    {
        // 1) Buscar anuncios activos no notificados
        $ads = SurpriseAd::where('is_active', 1)
            ->whereNull('notified_at')
            ->with('surprise')
            ->get();

        if ($ads->isEmpty()) {
            $this->info("No new ads to notify");
            return Command::SUCCESS;
        }

        // 2) Agrupar anuncios por skill
        $adsBySkill = $ads->groupBy(function ($ad) {
            return $ad->surprise->skill_id;
        });

        $totalNotifications = 0;

        // 3) Para cada skill, buscar genios que la tengan
        foreach ($adsBySkill as $skillId => $skillAds) {

            $geniuses = User::where('role', 'genius')
                ->where('skill_id', $skillId)
                ->get();

            foreach ($geniuses as $genius) {

                // Separar Premium y normales
                $premium = $skillAds->where('priority', 3);
                $normal = $skillAds->where('priority', '<', 3);

                if ($premium->count() === 0 && $normal->count() === 0) {
                    continue;
                }

                // 4) Generar mensaje
                if ($premium->count() > 0) {
                    $message = "Hay {$premium->count()} sorpresas Premium y {$normal->count()} destacadas nuevas que encajan contigo.";
                } else {
                    $message = "Hay {$normal->count()} nuevas sorpresas destacadas que encajan contigo.";
                }

                // 5) Enviar notificación interna
                NotificationEvents::adsSummary($genius, $message);

                $totalNotifications++;
            }

            // 6) Marcar anuncios como notificados
            foreach ($skillAds as $ad) {
                $ad->notified_at = now();
                $ad->save();
            }
        }

        $this->info("Notifications sent: {$totalNotifications}");

        return Command::SUCCESS;
    }
}