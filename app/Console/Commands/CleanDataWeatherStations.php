<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanDataWeatherStations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-data-weather-stations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia los datos actuales, diarios y horarios de las estaciones meteorológicas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Truncate weather station data tables
        \DB::table('current_observations')->truncate();
        \DB::table('daily_summaries')->truncate();
        \DB::table('hourly_observations')->truncate();

        $this->info('Datos de estaciones meteorológicas limpiados exitosamente.');
        $this->info('- Datos actuales: eliminados');
        $this->info('- Datos diarios: eliminados');
        $this->info('- Datos horarios: eliminados');
    }
}
