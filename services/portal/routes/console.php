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

/*
|--------------------------------------------------------------------------
| Regeneración Periódica del Sitemap (portal:sitemap)
|--------------------------------------------------------------------------
|
| Regenera cada noche a las 04:00 el archivo sitemap.xml con las rutas públicas
| e indexables del portal, con prevención de solapamiento.
|
*/
Schedule::command('portal:sitemap')
    ->dailyAt('04:00')
    ->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Precompilación Periódica de la Caché del Mapa (mapa:cache)
|--------------------------------------------------------------------------
|
| Genera cada 2 minutos los volcados JSON de nodos y estadísticas para entrega
| ultra-rápida en Nginx/Redis con prevención estricta de solapamiento.
|
*/
Schedule::command('mapa:cache')
    ->everyTwoMinutes()
    ->withoutOverlapping();

