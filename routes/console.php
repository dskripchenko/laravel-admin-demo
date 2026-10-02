<?php

use Illuminate\Support\Facades\Schedule;

/*
| The stand's schedule. One cron entry runs it all:
|     * * * * * php /path/to/artisan schedule:run
| (the Docker image runs `schedule:work` under supervisord instead).
*/

$resetCron = (string) config('demo.reset_cron');
if ($resetCron !== '') {
    Schedule::command('demo:reset --force')->cron($resetCron)->withoutOverlapping();
}

Schedule::command('admin:health:run')->everyMinute();
Schedule::command('admin:health:cleanup')->daily();
Schedule::command('admin:pulse:aggregate')->everyFiveMinutes();
Schedule::command('admin:pulse:rotate')->daily();
