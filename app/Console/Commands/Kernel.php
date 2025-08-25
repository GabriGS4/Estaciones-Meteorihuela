<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define el schedule de comandos.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejemplo: ejecutar comando cada 10 minutos
        $schedule->command('app:fetch-weather-data')->everyTenMinutes();
    }

    /**
     * Registra comandos Artisan.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
