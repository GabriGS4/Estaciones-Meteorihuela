<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Station;
use App\Models\CurrentObservation;
use App\Models\DailySummary;
use App\Models\HourlyObservation;
use Carbon\Carbon;
use Log;

class FetchWeatherData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-weather-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtiene datos actuales, diarios y horarios de todas las estaciones y los guarda en la BBDD';


    /**
     * Execute the console command.
     */
  public function handle()
    {
        $this->info("🚀 Iniciando actualización meteorológica...");

        $stations = Station::all();

        Log::info("Iniciando fetch de datos para " . $stations->count() . " estaciones a fecha y hora " . now());
        foreach ($stations as $station) {
            if (empty($station->station_id) || empty($station->api_key)) {
                $this->warn("⚠️ Estación {$station->id} no tiene station_id o api_key. Se omite.");
                continue;
            }
            $this->info("📡 Estación: {$station->station_id}");
            Log::info("📡 Estación: {$station->station_id}");

            try {
                // 1️⃣ Observación actual
                $urlCurrent = "https://api.weather.com/v2/pws/observations/current?stationId={$station->station_id}&format=json&units=m&apiKey={$station->api_key}";
                $dataCurrent = Http::timeout(10)->get($urlCurrent)->json();

                if (isset($dataCurrent['observations'][0])) {
                    $obs = $dataCurrent['observations'][0];

                    CurrentObservation::updateOrCreate(
                        [
                            'station_id' => $station->id,
                        ],
                        [
                            'obs_time_utc'   => Carbon::parse($obs['obsTimeUtc']),
                            'obs_time_local' => Carbon::parse($obs['obsTimeLocal']),
                            'software_type'  => $obs['softwareType'] ?? null,
                            'solar_radiation'=> $obs['solarRadiation'] ?? null,
                            'uv'             => $obs['uv'] ?? null,
                            'winddir'        => $obs['winddir'] ?? null,
                            'humidity'       => $obs['humidity'] ?? null,
                            'temp'           => $obs['metric']['temp'] ?? null,
                            'heat_index'     => $obs['metric']['heatIndex'] ?? null,
                            'dewpt'          => $obs['metric']['dewpt'] ?? null,
                            'wind_chill'     => $obs['metric']['windChill'] ?? null,
                            'wind_speed'     => $obs['metric']['windSpeed'] ?? null,
                            'wind_gust'      => $obs['metric']['windGust'] ?? null,
                            'pressure'       => $obs['metric']['pressure'] ?? null,
                            'precip_rate'    => $obs['metric']['precipRate'] ?? null,
                            'precip_total'   => $obs['metric']['precipTotal'] ?? null,
                            'qc_status'      => $obs['qcStatus'] ?? null,
                        ]
                    );
                }

                // 2️⃣ Daily Summary (últimos 7 días)
                $urlDaily = "https://api.weather.com/v2/pws/dailysummary/7day?stationId={$station->station_id}&format=json&units=m&apiKey={$station->api_key}";
                $dataDaily = Http::timeout(10)->get($urlDaily)->json();

                if (isset($dataDaily['summaries'])) {
                    foreach ($dataDaily['summaries'] as $sum) {
                        $obsTimeUtc = Carbon::parse($sum['obsTimeUtc']);
                        
                        // Buscar si ya existe un registro para esa estación en esa fecha
                        $existingSummary = DailySummary::where('station_id', $station->id)
                            ->whereDate('obs_time_utc', $obsTimeUtc->toDateString())
                            ->first();
                        
                        $summaryData = [
                            'obs_time_utc'        => $obsTimeUtc,
                            'obs_time_local'      => Carbon::parse($sum['obsTimeLocal']),
                            'solar_radiation_high'=> $sum['solarRadiationHigh'] ?? null,
                            'uv_high'             => $sum['uvHigh'] ?? null,
                            'winddir_avg'         => $sum['winddirAvg'] ?? null,
                            'humidity_high'       => $sum['humidityHigh'] ?? null,
                            'humidity_low'        => $sum['humidityLow'] ?? null,
                            'humidity_avg'        => $sum['humidityAvg'] ?? null,
                            'temp_high'           => $sum['metric']['tempHigh'] ?? null,
                            'temp_low'            => $sum['metric']['tempLow'] ?? null,
                            'temp_avg'            => $sum['metric']['tempAvg'] ?? null,
                            'windspeed_high'      => $sum['metric']['windspeedHigh'] ?? null,
                            'windspeed_low'       => $sum['metric']['windspeedLow'] ?? null,
                            'windspeed_avg'       => $sum['metric']['windspeedAvg'] ?? null,
                            'windgust_high'       => $sum['metric']['windgustHigh'] ?? null,
                            'windgust_low'        => $sum['metric']['windgustLow'] ?? null,
                            'windgust_avg'        => $sum['metric']['windgustAvg'] ?? null,
                            'dewpt_high'          => $sum['metric']['dewptHigh'] ?? null,
                            'dewpt_low'           => $sum['metric']['dewptLow'] ?? null,
                            'dewpt_avg'           => $sum['metric']['dewptAvg'] ?? null,
                            'windchill_high'      => $sum['metric']['windchillHigh'] ?? null,
                            'windchill_low'       => $sum['metric']['windchillLow'] ?? null,
                            'windchill_avg'       => $sum['metric']['windchillAvg'] ?? null,
                            'heatindex_high'      => $sum['metric']['heatindexHigh'] ?? null,
                            'heatindex_low'       => $sum['metric']['heatindexLow'] ?? null,
                            'heatindex_avg'       => $sum['metric']['heatindexAvg'] ?? null,
                            'pressure_max'        => $sum['metric']['pressureMax'] ?? null,
                            'pressure_min'        => $sum['metric']['pressureMin'] ?? null,
                            'pressure_trend'      => $sum['metric']['pressureTrend'] ?? null,
                            'precip_rate'         => $sum['metric']['precipRate'] ?? null,
                            'precip_total'        => $sum['metric']['precipTotal'] ?? null,
                            'qc_status'           => $sum['qcStatus'] ?? null,
                        ];
                        
                        if ($existingSummary) {
                            // Actualizar el registro existente
                            $existingSummary->update($summaryData);
                        } else {
                            // Crear nuevo registro
                            DailySummary::create(array_merge(['station_id' => $station->id], $summaryData));
                        }
                    }
                }

                // 3️⃣ Histórico horario (últimas 24h)
                $date = now()->format('Ymd'); // YYYYMMDD
                $urlHourly = "https://api.weather.com/v2/pws/history/hourly?stationId={$station->station_id}&format=json&units=m&date={$date}&apiKey={$station->api_key}";
                $dataHourly = Http::timeout(10)->get($urlHourly)->json();

                if (isset($dataHourly['observations'])) {
                    foreach ($dataHourly['observations'] as $h) {
                        HourlyObservation::updateOrCreate(
                            [
                                'station_id'   => $station->id,
                                'obs_time_utc' => Carbon::parse($h['obsTimeUtc']),
                            ],
                            [
                                'obs_time_local'      => Carbon::parse($h['obsTimeLocal']),
                                'solar_radiation_high'=> $h['solarRadiationHigh'] ?? null,
                                'uv_high'             => $h['uvHigh'] ?? null,
                                'winddir_avg'         => $h['winddirAvg'] ?? null,
                                'humidity_high'       => $h['humidityHigh'] ?? null,
                                'humidity_low'        => $h['humidityLow'] ?? null,
                                'humidity_avg'        => $h['humidityAvg'] ?? null,
                                'temp_high'           => $h['metric']['tempHigh'] ?? null,
                                'temp_low'            => $h['metric']['tempLow'] ?? null,
                                'temp_avg'            => $h['metric']['tempAvg'] ?? null,
                                'windspeed_high'      => $h['metric']['windspeedHigh'] ?? null,
                                'windspeed_low'       => $h['metric']['windspeedLow'] ?? null,
                                'windspeed_avg'       => $h['metric']['windspeedAvg'] ?? null,
                                'windgust_high'       => $h['metric']['windgustHigh'] ?? null,
                                'windgust_low'        => $h['metric']['windgustLow'] ?? null,
                                'windgust_avg'        => $h['metric']['windgustAvg'] ?? null,
                                'dewpt_high'          => $h['metric']['dewptHigh'] ?? null,
                                'dewpt_low'           => $h['metric']['dewptLow'] ?? null,
                                'dewpt_avg'           => $h['metric']['dewptAvg'] ?? null,
                                'windchill_high'      => $h['metric']['windchillHigh'] ?? null,
                                'windchill_low'       => $h['metric']['windchillLow'] ?? null,
                                'windchill_avg'       => $h['metric']['windchillAvg'] ?? null,
                                'heatindex_high'      => $h['metric']['heatindexHigh'] ?? null,
                                'heatindex_low'       => $h['metric']['heatindexLow'] ?? null,
                                'heatindex_avg'       => $h['metric']['heatindexAvg'] ?? null,
                                'pressure_max'        => $h['metric']['pressureMax'] ?? null,
                                'pressure_min'        => $h['metric']['pressureMin'] ?? null,
                                'pressure_trend'      => $h['metric']['pressureTrend'] ?? null,
                                'precip_rate'         => $h['metric']['precipRate'] ?? null,
                                'precip_total'        => $h['metric']['precipTotal'] ?? null,
                                'qc_status'           => $h['qcStatus'] ?? null,
                            ]
                        );
                    }
                }

                $this->info("✅ Datos guardados para {$station->station_id}");
                Log::info("Datos guardados para {$station->station_id}");

            } catch (\Exception $e) {
                $this->error("❌ Error en {$station->station_id}: " . $e->getMessage());
                Log::error("Error en {$station->station_id}: " . $e->getMessage());
            }
        }

        $this->info("🎉 Proceso completado.");
        Log::info("Proceso completado a fecha y hora: " . now());
    }

}
