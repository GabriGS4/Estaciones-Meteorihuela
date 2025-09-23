<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDataWeatherStations extends Command
{
    protected $signature = 'app:clean-data-weather-stations';
    protected $description = 'Limpia los datos actuales, diarios y horarios de las estaciones meteorológicas';

    public function handle()
    {
        try {
            $tables = [
                'current_observations' => 'Datos actuales',
                'daily_summaries'      => 'Datos diarios',
                'hourly_observations'  => 'Datos horarios',
            ];

            foreach ($tables as $table => $label) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->info("✅ {$label}: eliminados");
                } else {
                    $this->warn("⚠️ La tabla {$table} no existe, se omite.");
                }
            }

            $this->info('🎉 Limpieza completada con éxito.');
        } catch (\Exception $e) {
            $this->error("❌ Error al limpiar: " . $e->getMessage());
        }
    }
}
