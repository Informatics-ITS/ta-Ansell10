<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::exec('python app/Scraper/detikhealth.py')
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/scraper.log'));

Schedule::command('meal:remind')
            ->hourly()
            ->between('07:00', '22:00');