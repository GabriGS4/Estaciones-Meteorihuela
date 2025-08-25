<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


// Programar el comando de datos meteorológicos para ejecutarse cada 10 minutos
Schedule::command('app:fetch-weather-data')->everyTenMinutes();
