<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('surprises:check-deadlines')->hourly();
Schedule::command('surprise_ads:notify')->everyThreeHours();
Schedule::command('surprise_ads:expire')->hourly();
