<?php

declare(strict_types=1);

use App\Jobs\ComprobarServicios;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tareas Programadas del Portal (portal-tareas)
|--------------------------------------------------------------------------
|
| Comprobación activa de salud de todos los componentes de la red cada minuto,
| con prevención de solapamiento. Conforme a docs/info/portal/14-operator-panel.md.
|
*/
Schedule::job(new ComprobarServicios)
    ->everyMinute()
    ->withoutOverlapping();
