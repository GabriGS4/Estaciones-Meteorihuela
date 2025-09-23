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
use DB;

class FetchWeatherData extends Command
{
    protected $signature = 'app:fetch-weather-data';
    protected $description = 'Obtiene datos actuales, diarios y horarios de todas las estaciones y los guarda en la BBDD';

    public function handle()
    {
        $this->fetchWeatherDataAPI();
        $this->fetchWeatherDataEcowitt();
    }

    private function fetchWeatherDataAPI()
    {
        $this->info("🚀 Iniciando actualización meteorológica...");
        $stations = Station::where('use_ecowitt', false)->get();
        Log::info("Iniciando fetch de datos para " . $stations->count() . " estaciones a fecha y hora " . now());

        foreach ($stations as $station) {
            if (empty($station->station_id) || empty($station->api_key)) {
                $this->warn("⚠️ Estación {$station->id} no tiene station_id o api_key. Se omite.");
                continue;
            }

            $this->info("📡 Estación: {$station->station_id}");
            try {
                // 1️⃣ Observación actual
                $urlCurrent = "https://api.weather.com/v2/pws/observations/current?stationId={$station->station_id}&format=json&units=m&apiKey={$station->api_key}";
                $dataCurrent = Http::timeout(10)->get($urlCurrent)->json();

                if (isset($dataCurrent['observations'][0])) {
                    $obs = $dataCurrent['observations'][0];
                    CurrentObservation::updateOrCreate(
                        ['station_id' => $station->id],
                        [
                            'obs_time_utc'   => Carbon::parse($obs['obsTimeUtc'])->toDateTimeString(),
                            'obs_time_local' => Carbon::parse($obs['obsTimeLocal'])->toDateTimeString(),
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

                // 2️⃣ Daily Summary (últimos 7 días) con UPSERT y normalización a startOfDay
                $urlDaily = "https://api.weather.com/v2/pws/dailysummary/7day?stationId={$station->station_id}&format=json&units=m&apiKey={$station->api_key}";
                $dataDaily = Http::timeout(10)->get($urlDaily)->json();

                if (!empty($dataDaily['summaries'])) {
                    $assocDaily = []; // clave única -> registro (evita duplicados)
                    foreach ($dataDaily['summaries'] as $sum) {
                        $obsTimeUtc = Carbon::parse($sum['obsTimeUtc'])->startOfDay();
                        $key = $station->id . '|' . $obsTimeUtc->toDateString(); // date-only key

                        $assocDaily[$key] = [ // si se repite, la última entrada sobrescribe (evita duplicados)
                            'station_id'          => $station->id,
                            'obs_time_utc'        => $obsTimeUtc->toDateTimeString(),
                            'obs_time_local'      => Carbon::parse($sum['obsTimeLocal'])->startOfDay()->toDateTimeString(),
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
                            'updated_at'          => now(),
                        ];
                    }

                    if (!empty($assocDaily)) {
                        DailySummary::upsert(
                            array_values($assocDaily),
                            ['station_id', 'obs_time_utc'],
                            [
                                'obs_time_local','solar_radiation_high','uv_high','winddir_avg',
                                'humidity_high','humidity_low','humidity_avg',
                                'temp_high','temp_low','temp_avg',
                                'windspeed_high','windspeed_low','windspeed_avg',
                                'windgust_high','windgust_low','windgust_avg',
                                'dewpt_high','dewpt_low','dewpt_avg',
                                'windchill_high','windchill_low','windchill_avg',
                                'heatindex_high','heatindex_low','heatindex_avg',
                                'pressure_max','pressure_min','pressure_trend',
                                'precip_rate','precip_total','qc_status','updated_at'
                            ]
                        );
                    }
                }

                // 3️⃣ Histórico horario (últimas 24h) con UPSERT normalizando a hora en punto
                $urlHourly = "https://api.weather.com/v2/pws/observations/all/1day?stationId={$station->station_id}&format=json&units=m&apiKey={$station->api_key}";
                $dataHourly = Http::timeout(10)->get($urlHourly)->json();

                if (!empty($dataHourly['observations'])) {
                    $assocHourly = [];
                    foreach ($dataHourly['observations'] as $h) {
                        $obsTimeLocal = Carbon::parse($h['obsTimeLocal']);
                        // normalizar a hora exacta
                        $obsHour = $obsTimeLocal->copy()->setMinutes(0)->setSeconds(0);
                        if ($obsHour->minute !== 0) {
                            continue;
                        }
                        $key = $station->id . '|' . $obsHour->toDateTimeString();

                        $assocHourly[$key] = [
                            'station_id'           => $station->id,
                            'obs_time_local'       => $obsHour->toDateTimeString(),
                            'obs_time_utc'         => Carbon::parse($h['obsTimeUtc'])->copy()->setMinutes(0)->setSeconds(0)->toDateTimeString(),
                            'solar_radiation_high' => $h['solarRadiationHigh'] ?? null,
                            'uv_high'              => $h['uvHigh'] ?? null,
                            'winddir_avg'          => $h['winddirAvg'] ?? null,
                            'humidity_high'        => $h['humidityHigh'] ?? null,
                            'humidity_low'         => $h['humidityLow'] ?? null,
                            'humidity_avg'         => $h['humidityAvg'] ?? null,
                            'temp_high'            => $h['metric']['tempHigh'] ?? null,
                            'temp_low'             => $h['metric']['tempLow'] ?? null,
                            'temp_avg'             => $h['metric']['tempAvg'] ?? null,
                            'windspeed_high'       => $h['metric']['windspeedHigh'] ?? null,
                            'windspeed_low'        => $h['metric']['windspeedLow'] ?? null,
                            'windspeed_avg'        => $h['metric']['windspeedAvg'] ?? null,
                            'windgust_high'        => $h['metric']['windgustHigh'] ?? null,
                            'windgust_low'         => $h['metric']['windgustLow'] ?? null,
                            'windgust_avg'         => $h['metric']['windgustAvg'] ?? null,
                            'dewpt_high'           => $h['metric']['dewptHigh'] ?? null,
                            'dewpt_low'            => $h['metric']['dewptLow'] ?? null,
                            'dewpt_avg'            => $h['metric']['dewptAvg'] ?? null,
                            'windchill_high'       => $h['metric']['windchillHigh'] ?? null,
                            'windchill_low'        => $h['metric']['windchillLow'] ?? null,
                            'windchill_avg'        => $h['metric']['windchillAvg'] ?? null,
                            'heatindex_high'       => $h['metric']['heatindexHigh'] ?? null,
                            'heatindex_low'        => $h['metric']['heatindexLow'] ?? null,
                            'heatindex_avg'        => $h['metric']['heatindexAvg'] ?? null,
                            'pressure_max'         => $h['metric']['pressureMax'] ?? null,
                            'pressure_min'         => $h['metric']['pressureMin'] ?? null,
                            'pressure_trend'       => $h['metric']['pressureTrend'] ?? null,
                            'precip_rate'          => $h['metric']['precipRate'] ?? null,
                            'precip_total'         => $h['metric']['precipTotal'] ?? null,
                            'qc_status'            => $h['qcStatus'] ?? null,
                            'updated_at'           => now(),
                        ];
                    }

                    if (!empty($assocHourly)) {
                        HourlyObservation::upsert(
                            array_values($assocHourly),
                            ['station_id', 'obs_time_local'],
                            [
                                'obs_time_utc','solar_radiation_high','uv_high','winddir_avg',
                                'humidity_high','humidity_low','humidity_avg',
                                'temp_high','temp_low','temp_avg',
                                'windspeed_high','windspeed_low','windspeed_avg',
                                'windgust_high','windgust_low','windgust_avg',
                                'dewpt_high','dewpt_low','dewpt_avg',
                                'windchill_high','windchill_low','windchill_avg',
                                'heatindex_high','heatindex_low','heatindex_avg',
                                'pressure_max','pressure_min','pressure_trend',
                                'precip_rate','precip_total','qc_status','updated_at'
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

    private function fetchWeatherDataEcowitt()
    {
        $this->info("🚀 Iniciando actualización meteorológica Ecowitt...");
        $stations = Station::where('use_ecowitt', true)->get();
        Log::info("Iniciando fetch de datos Ecowitt para " . $stations->count() ." estaciones a fecha y hora " . now());

        $applicationKeys = env('APPLICATION_KEY_ECOWITT');
        $apiKeys = env('API_KEY_ECOWITT');

        if (!is_array($applicationKeys)) $applicationKeys = explode(',', $applicationKeys);
        if (!is_array($apiKeys)) $apiKeys = explode(',', $apiKeys);

        if (empty($applicationKeys) || empty($apiKeys)) {
            $this->error("❌ No se encontraron las claves de Ecowitt en el .env");
            Log::error("No se encontraron APPLICATION_KEY_ECOWITT o API_KEY_ECOWITT en el .env");
            return;
        }

        $appKeyIndex = 0;
        $apiKeyIndex = 0;

        foreach ($stations as $station) {
            if (empty($station->api_key)) {
                $this->warn("⚠️ Estación Ecowitt {$station->id} no tiene MAC. Se omite.");
                continue;
            }

            $mac = $station->api_key;
            $this->info("📡 Estación Ecowitt: {$station->name} (MAC: {$mac})");

            try {
                // 1️⃣ Observación actual Ecowitt
                $urlCurrent = "https://api.ecowitt.net/api/v3/device/real_time?application_key={APPLICATION_KEY}&api_key={API_KEY}&mac={$mac}&call_back=all&temp_unitid=1&pressure_unitid=3&wind_speed_unitid=7&rainfall_unitid=12";
                $dataCurrent = $this->makeEcowittRequest($urlCurrent, $applicationKeys, $apiKeys, $appKeyIndex, $apiKeyIndex);

                if ($dataCurrent && isset($dataCurrent['code']) && $dataCurrent['code'] === 0 && isset($dataCurrent['data'])) {
                    $data = $dataCurrent['data'];
                    $timestamp = Carbon::createFromTimestamp($dataCurrent['time']);

                    CurrentObservation::updateOrCreate(
                        ['station_id' => $station->id],
                        [
                            'obs_time_utc'   => $timestamp->toDateTimeString(),
                            'obs_time_local' => $timestamp->toDateTimeString(),
                            'software_type'  => 'Ecowitt',
                            'solar_radiation'=> $data['solar_and_uvi']['solar']['value'] ?? null,
                            'uv'             => $data['solar_and_uvi']['uvi']['value'] ?? null,
                            'winddir'        => $data['wind']['wind_direction']['value'] ?? null,
                            'humidity'       => $data['outdoor']['humidity']['value'] ?? null,
                            'temp'           => $data['outdoor']['temperature']['value'] ?? null,
                            'heat_index'     => $data['outdoor']['feels_like']['value'] ?? null,
                            'dewpt'          => $data['outdoor']['dew_point']['value'] ?? null,
                            'wind_chill'     => $data['outdoor']['app_temp']['value'] ?? null,
                            'wind_speed'     => $data['wind']['wind_speed']['value'] ?? null,
                            'wind_gust'      => $data['wind']['wind_gust']['value'] ?? null,
                            'pressure'       => $data['pressure']['relative']['value'] ?? null,
                            'precip_rate'    => $data['rainfall']['rain_rate']['value'] ?? null,
                            'precip_total'   => $data['rainfall']['daily']['value'] ?? null,
                            'qc_status'      => 1,
                        ]
                    );
                } else {
                    $this->warn("⚠️ No se pudieron obtener datos actuales de Ecowitt para {$station->name}.");
                    Log::warning("No se pudieron obtener datos actuales de Ecowitt para {$station->name}", $dataCurrent ?: []);
                }

                // 2️⃣ Daily Ecowitt -> procesado con startOfDay
                $endDate = Carbon::now();
                $startDate = Carbon::now()->subDays(7);
                $urlDaily = "https://api.ecowitt.net/api/v3/device/history?application_key={APPLICATION_KEY}&api_key={API_KEY}&mac={$mac}&start_date={$startDate->format('Y-m-d H:i:s')}&end_date={$endDate->format('Y-m-d H:i:s')}&cycle_type=1day&call_back=outdoor,solar_and_uvi,wind,pressure,rainfall&temp_unitid=1&pressure_unitid=3&wind_speed_unitid=7&rainfall_unitid=12";
                $dataDaily = $this->makeEcowittRequest($urlDaily, $applicationKeys, $apiKeys, $appKeyIndex, $apiKeyIndex);

                if ($dataDaily && isset($dataDaily['code']) && $dataDaily['code'] === 0 && isset($dataDaily['data'])) {
                    $this->processEcowittDailyData($station, $dataDaily['data']);
                } else {
                    Log::warning("Error en datos diarios de Ecowitt para {$station->name}", $dataDaily ?: []);
                }

                // 3️⃣ Hourly Ecowitt -> procesado truncando a hora exacta
                $startDateHourly = Carbon::now()->subHours(24);
                $endDateHourly = Carbon::now();
                $urlHourly = "https://api.ecowitt.net/api/v3/device/history?application_key={APPLICATION_KEY}&api_key={API_KEY}&mac={$mac}&start_date={$startDateHourly->format('Y-m-d H:i:s')}&end_date={$endDateHourly->format('Y-m-d H:i:s')}&cycle_type=auto&call_back=outdoor,solar_and_uvi,wind,pressure,rainfall&temp_unitid=1&pressure_unitid=3&wind_speed_unitid=7&rainfall_unitid=12";
                $dataHourly = $this->makeEcowittRequest($urlHourly, $applicationKeys, $apiKeys, $appKeyIndex, $apiKeyIndex);

                if ($dataHourly && isset($dataHourly['code']) && $dataHourly['code'] === 0 && isset($dataHourly['data'])) {
                    $this->processEcowittHourlyData($station, $dataHourly['data']);
                } else {
                    Log::warning("Error en datos horarios de Ecowitt para {$station->name}", $dataHourly ?: []);
                }

                $this->info("✅ Datos Ecowitt guardados para {$station->name}");
            } catch (\Exception $e) {
                $this->error("❌ Error en estación Ecowitt {$station->name}: " . $e->getMessage());
                Log::error("Error en estación Ecowitt {$station->name}: " . $e->getMessage());
            }
        }

        $this->info("🎉 Proceso Ecowitt completado.");
        Log::info("Proceso Ecowitt completado a fecha y hora: " . now());
    }

    private function processEcowittDailyData($station, $data)
    {
        $assoc = [];

        // obtener timestamps
        $timestamps = [];
        if (isset($data['outdoor']['temperature']['list'])) {
            $timestamps = array_keys($data['outdoor']['temperature']['list']);
        }

        foreach ($timestamps as $ts) {
            $obsTime = Carbon::createFromTimestamp($ts)->startOfDay();
            $key = $station->id . '|' . $obsTime->toDateString();

            $assoc[$key] = [
                'station_id'          => $station->id,
                'obs_time_utc'        => $obsTime->toDateTimeString(),
                'obs_time_local'      => $obsTime->toDateTimeString(),
                'solar_radiation_high'=> $this->getEcowittValue($data, 'solar_and_uvi.solar.list', $ts),
                'uv_high'             => $this->getEcowittValue($data, 'solar_and_uvi.uvi.list', $ts),
                'winddir_avg'         => $this->getEcowittValue($data, 'wind.wind_direction.list', $ts),
                'humidity_high'       => $this->getEcowittValue($data, 'outdoor.humidity.list', $ts),
                'humidity_low'        => $this->getEcowittValue($data, 'outdoor.humidity.list', $ts),
                'humidity_avg'        => $this->getEcowittValue($data, 'outdoor.humidity.list', $ts),
                'temp_high'           => $this->getEcowittValue($data, 'outdoor.temperature.list', $ts),
                'temp_low'            => $this->getEcowittValue($data, 'outdoor.temperature.list', $ts),
                'temp_avg'            => $this->getEcowittValue($data, 'outdoor.temperature.list', $ts),
                'windspeed_high'      => $this->getEcowittValue($data, 'wind.wind_speed.list', $ts),
                'windspeed_low'       => $this->getEcowittValue($data, 'wind.wind_speed.list', $ts),
                'windspeed_avg'       => $this->getEcowittValue($data, 'wind.wind_speed.list', $ts),
                'windgust_high'       => $this->getEcowittValue($data, 'wind.wind_gust.list', $ts),
                'windgust_low'        => $this->getEcowittValue($data, 'wind.wind_gust.list', $ts),
                'windgust_avg'        => $this->getEcowittValue($data, 'wind.wind_gust.list', $ts),
                'dewpt_high'          => $this->getEcowittValue($data, 'outdoor.dew_point.list', $ts),
                'dewpt_low'           => $this->getEcowittValue($data, 'outdoor.dew_point.list', $ts),
                'dewpt_avg'           => $this->getEcowittValue($data, 'outdoor.dew_point.list', $ts),
                'heatindex_high'      => $this->getEcowittValue($data, 'outdoor.feels_like.list', $ts),
                'heatindex_low'       => $this->getEcowittValue($data, 'outdoor.feels_like.list', $ts),
                'heatindex_avg'       => $this->getEcowittValue($data, 'outdoor.feels_like.list', $ts),
                'pressure_max'        => $this->getEcowittValue($data, 'pressure.relative.list', $ts),
                'pressure_min'        => $this->getEcowittValue($data, 'pressure.relative.list', $ts),
                'precip_rate'         => $this->getEcowittValue($data, 'rainfall.rain_rate.list', $ts),
                'precip_total'        => $this->getEcowittValue($data, 'rainfall.daily.list', $ts),
                'qc_status'           => 1,
                'updated_at'          => now(),
            ];
        }

        if (!empty($assoc)) {
            DailySummary::upsert(
                array_values($assoc),
                ['station_id', 'obs_time_utc'],
                [
                    'obs_time_local','solar_radiation_high','uv_high','winddir_avg',
                    'humidity_high','humidity_low','humidity_avg',
                    'temp_high','temp_low','temp_avg',
                    'windspeed_high','windspeed_low','windspeed_avg',
                    'windgust_high','windgust_low','windgust_avg',
                    'dewpt_high','dewpt_low','dewpt_avg',
                    'heatindex_high','heatindex_low','heatindex_avg',
                    'pressure_max','pressure_min','precip_rate','precip_total','qc_status','updated_at'
                ]
            );
        }
    }

    private function processEcowittHourlyData($station, $data)
    {
        $assoc = [];

        $timestamps = [];
        if (isset($data['outdoor']['temperature']['list'])) {
            $timestamps = array_keys($data['outdoor']['temperature']['list']);
        }

        foreach ($timestamps as $ts) {
            $obsTime = Carbon::createFromTimestamp($ts);
            $obsHour = $obsTime->copy()->setMinutes(0)->setSeconds(0);

            if ($obsHour->minute !== 0) continue;

            $key = $station->id . '|' . $obsHour->toDateTimeString();

            $assoc[$key] = [
                'station_id'           => $station->id,
                'obs_time_local'       => $obsHour->toDateTimeString(),
                'obs_time_utc'         => $obsHour->toDateTimeString(),
                'solar_radiation_high' => $this->getEcowittValue($data, 'solar_and_uvi.solar.list', $ts),
                'uv_high'              => $this->getEcowittValue($data, 'solar_and_uvi.uvi.list', $ts),
                'winddir_avg'          => $this->getEcowittValue($data, 'wind.wind_direction.list', $ts),
                'humidity_high'        => $this->getEcowittValue($data, 'outdoor.humidity.list', $ts),
                'humidity_low'         => $this->getEcowittValue($data, 'outdoor.humidity.list', $ts),
                'humidity_avg'         => $this->getEcowittValue($data, 'outdoor.humidity.list', $ts),
                'temp_high'            => $this->getEcowittValue($data, 'outdoor.temperature.list', $ts),
                'temp_low'             => $this->getEcowittValue($data, 'outdoor.temperature.list', $ts),
                'temp_avg'             => $this->getEcowittValue($data, 'outdoor.temperature.list', $ts),
                'windspeed_high'       => $this->getEcowittValue($data, 'wind.wind_speed.list', $ts),
                'windspeed_low'        => $this->getEcowittValue($data, 'wind.wind_speed.list', $ts),
                'windspeed_avg'        => $this->getEcowittValue($data, 'wind.wind_speed.list', $ts),
                'windgust_high'        => $this->getEcowittValue($data, 'wind.wind_gust.list', $ts),
                'windgust_low'         => $this->getEcowittValue($data, 'wind.wind_gust.list', $ts),
                'windgust_avg'         => $this->getEcowittValue($data, 'wind.wind_gust.list', $ts),
                'dewpt_high'           => $this->getEcowittValue($data, 'outdoor.dew_point.list', $ts),
                'dewpt_low'            => $this->getEcowittValue($data, 'outdoor.dew_point.list', $ts),
                'dewpt_avg'            => $this->getEcowittValue($data, 'outdoor.dew_point.list', $ts),
                'heatindex_high'       => $this->getEcowittValue($data, 'outdoor.feels_like.list', $ts),
                'heatindex_low'        => $this->getEcowittValue($data, 'outdoor.feels_like.list', $ts),
                'heatindex_avg'        => $this->getEcowittValue($data, 'outdoor.feels_like.list', $ts),
                'pressure_max'         => $this->getEcowittValue($data, 'pressure.relative.list', $ts),
                'pressure_min'         => $this->getEcowittValue($data, 'pressure.relative.list', $ts),
                'precip_rate'          => $this->getEcowittValue($data, 'rainfall.rain_rate.list', $ts),
                'precip_total'         => $this->getEcowittValue($data, 'rainfall.hourly.list', $ts),
                'qc_status'            => 1,
                'updated_at'           => now(),
            ];
        }

        if (!empty($assoc)) {
            HourlyObservation::upsert(
                array_values($assoc),
                ['station_id', 'obs_time_local'],
                [
                    'obs_time_utc','solar_radiation_high','uv_high','winddir_avg',
                    'humidity_high','humidity_low','humidity_avg',
                    'temp_high','temp_low','temp_avg',
                    'windspeed_high','windspeed_low','windspeed_avg',
                    'windgust_high','windgust_low','windgust_avg',
                    'dewpt_high','dewpt_low','dewpt_avg',
                    'heatindex_high','heatindex_low','heatindex_avg',
                    'pressure_max','pressure_min',
                    'precip_rate','precip_total','qc_status','updated_at'
                ]
            );
        }
    }

    private function getEcowittValue($data, $path, $timestamp)
    {
        $keys = explode('.', $path);
        $current = $data;
        foreach ($keys as $k) {
            if (!isset($current[$k])) return null;
            $current = $current[$k];
        }
        return (is_array($current) && isset($current[$timestamp])) ? $current[$timestamp] : null;
    }

    private function makeEcowittRequest($urlTemplate, $applicationKeys, $apiKeys, &$appKeyIndex, &$apiKeyIndex)
    {
        $maxAttempts = max(count($applicationKeys), count($apiKeys));
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $currentAppKey = $applicationKeys[$appKeyIndex % count($applicationKeys)];
            $currentApiKey = $apiKeys[$apiKeyIndex % count($apiKeys)];
            $url = str_replace(['{APPLICATION_KEY}', '{API_KEY}'], [$currentAppKey, $currentApiKey], $urlTemplate);
            try {
                $response = Http::timeout(15)->get($url);
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['code']) && $data['code'] === -1) {
                        $appKeyIndex = ($appKeyIndex + 1) % count($applicationKeys);
                        $apiKeyIndex = ($apiKeyIndex + 1) % count($apiKeys);
                        continue;
                    }
                    return $data;
                }
            } catch (\Exception $e) {
                Log::warning("Excepción Ecowitt: {$e->getMessage()}");
            }
            $appKeyIndex = ($appKeyIndex + 1) % count($applicationKeys);
            $apiKeyIndex = ($apiKeyIndex + 1) % count($apiKeys);
        }

        Log::error("Todas las combinaciones de claves de Ecowitt fallaron");
        return null;
    }
}
