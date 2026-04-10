<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Surprise;
use App\Helpers\Notify;
use Carbon\Carbon;

class CheckSurpriseDeadlines extends Command
{
    protected $signature = 'surprises:check-deadlines';
    protected $description = 'Send notifications when surprises are close to deadline';

    public function handle()
    {
        $now = Carbon::now();

        $surprises = Surprise::whereNotNull('deadline')
            ->where('deadline_warning_sent', 0)
            ->get();

        foreach ($surprises as $surprise) {

            $hoursLeft = $now->diffInHours($surprise->deadline, false);

            if ($hoursLeft <= 24 && $hoursLeft > 0) {

                // Notificar al creador
                Notify::warning(
                    $surprise->creator_id,
                    'Sorpresa urgente',
                    'Faltan menos de 24 horas para la entrega.'
                );

                // Notificar al genius si existe
                if ($surprise->genius_id) {
                    Notify::warning(
                        $surprise->genius_id,
                        'Sorpresa urgente',
                        'Faltan menos de 24 horas para entregar la sorpresa.'
                    );
                }

                // Marcar como enviada
                $surprise->deadline_warning_sent = 1;
                $surprise->save();
            }
        }

        return 0;
    }
}
