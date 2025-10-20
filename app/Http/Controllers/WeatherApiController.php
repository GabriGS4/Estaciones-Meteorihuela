<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\CurrentObservation;
use App\Models\DailySummary;
use App\Models\HourlyObservation;
use Illuminate\Support\Facades\URL;

class WeatherApiController extends Controller
{
    /**
     * Obtener todas las estaciones disponibles
     */
    public function stations()
    {
        $stations = Station::all();
        
        return response()->json([
            'success' => true,
            'data' => $stations,
            'count' => $stations->count()
        ]);
    }

    /**
     * Obtener todas las observaciones actuales
     */
    public function current()
    {
        $observations = CurrentObservation::with('station')->get();
        
        return response()->json([
            'success' => true,
            'data' => $observations,
            'count' => $observations->count()
        ]);
    }

    /**
     * Obtener observación actual por estación
     */
    public function currentByStation($stationId)
    {
        $observation = CurrentObservation::with('station')
            ->where('station_id', $stationId)
            ->first();

        if (!$observation) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos actuales para esta estación'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $observation
        ]);
    }

    /**
     * Obtener todos los resúmenes diarios
     */
    public function daily(Request $request)
    {
        $query = DailySummary::with('station')
            ->orderBy('obs_time_utc', 'desc');

        // Filtro opcional por número de días
        if ($request->has('days')) {
            $days = max(1, (int) $request->get('days')); // Mínimo 1 día
            $query->where('obs_time_utc', '>=', now()->subDays($days));
        }

        $summaries = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $summaries,
            'count' => $summaries->count()
        ]);
    }

    /**
     * Obtener resúmenes diarios por estación
     */
    public function dailyByStation(Request $request, $stationId)
    {
        $query = DailySummary::with('station')
            ->where('station_id', $stationId)
            ->orderBy('obs_time_utc', 'desc');

        // Filtro opcional por número de días
        if ($request->has('days')) {
            $days = max(1, (int) $request->get('days')); // Mínimo 1 día
            $query->where('obs_time_utc', '>=', now()->subDays($days));
        }

        $summaries = $query->get();

        if ($summaries->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos diarios para esta estación'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $summaries,
            'count' => $summaries->count()
        ]);
    }

    /**
     * Obtener todas las observaciones horarias
     */
    public function hourly(Request $request)
    {
        $query = HourlyObservation::with('station')
            ->orderBy('obs_time_utc', 'desc');

        // Filtro opcional por número de horas
        if ($request->has('hours')) {
            $hours = max(1, (int) $request->get('hours')); // Mínimo 1 hora
            $query->where('obs_time_utc', '>=', now()->subHours($hours));
        }

        $observations = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $observations,
            'count' => $observations->count()
        ]);
    }

    /**
     * Obtener observaciones horarias por estación
     */
    public function hourlyByStation(Request $request, $stationId)
    {
        $query = HourlyObservation::with('station')
            ->where('station_id', $stationId)
            ->orderBy('obs_time_utc', 'desc');

        // Filtro opcional por número de horas
        if ($request->has('hours')) {
            $hours = max(1, (int) $request->get('hours')); // Mínimo 1 hora
            $query->where('obs_time_utc', '>=', now()->subHours($hours));
        }

        $observations = $query->get();

        if ($observations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos horarios para esta estación'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $observations,
            'count' => $observations->count()
        ]);
    }

    /**
     * Devuelve un iframe que carga la página embebible del mapa con todas las estaciones
     */
    public function map()
    {
        // URL pública que sirve el mapa embebible
        $embedUrl = url('/map/embed');

        $iframe = "<iframe src=\"{$embedUrl}\" style=\"width:100%;height:600px;border:0;\" title=\"Mapa de estaciones\"></iframe>";

        return response($iframe, 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Página embebible que muestra el mapa con Leaflet y todos los marcadores
     */
    public function mapEmbed()
    {
        // Cargar estaciones con su última observación actual y último resumen diario usando relaciones "latest"
        $stations = Station::whereNotNull('lat')
            ->whereNotNull('lon')
            ->with(['latestCurrentObservation', 'latestDailySummary'])
            ->get();

        $data = $stations->map(function ($s) {
            $current = $s->latestCurrentObservation;
            $daily = $s->latestDailySummary;

            // Fallback: si la relación no devuelve nada, hacer una consulta directa
            if (!$current) {
                $current = CurrentObservation::where('station_id', $s->id)
                    ->orderBy('obs_time_utc', 'desc')
                    ->first();
            }
            if (!$daily) {
                $daily = DailySummary::where('station_id', $s->id)
                    ->orderBy('obs_time_utc', 'desc')
                    ->first();
            }

            return [
                'id' => $s->id,
                'name' => $s->name,
                'lat' => (float) $s->lat,
                'lon' => (float) $s->lon,
                'temp' => $current ? $current->temp : null,
                'temp_min' => $daily ? $daily->temp_low : null,
                'temp_max' => $daily ? $daily->temp_high : null,
                'humidity' => $current?->humidity,
                'wind_gust' => $current?->wind_gust,
                'precip_total' => $daily?->precip_total,
            ];
        });

        return response()->view('map_embed', [
            'stations' => $data,
            'stationsJson' => $data->toJson(),
        ]);
    }

    /**
     * Endpoint de depuración: devuelve los datos que se usan para el mapa en JSON
     */
    public function mapData()
    {
        $stations = Station::whereNotNull('lat')
            ->whereNotNull('lon')
            ->with(['latestCurrentObservation', 'latestDailySummary'])
            ->get();

        $data = $stations->map(function ($s) {
            $current = $s->latestCurrentObservation;
            $daily = $s->latestDailySummary;

            if (!$current) {
                $current = CurrentObservation::where('station_id', $s->id)
                    ->orderBy('obs_time_utc', 'desc')
                    ->first();
            }
            if (!$daily) {
                $daily = DailySummary::where('station_id', $s->id)
                    ->orderBy('obs_time_utc', 'desc')
                    ->first();
            }

            return [
                'id' => $s->id,
                'name' => $s->name,
                'lat' => (float) $s->lat,
                'lon' => (float) $s->lon,
                'temp' => $current ? $current->temp : null,
                'temp_min' => $daily ? $daily->temp_low : null,
                'temp_max' => $daily ? $daily->temp_high : null,
                'humidity' => $current?->humidity,
                'wind_gust' => $current?->wind_gust,
                'precip_total' => $daily?->precip_total,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'data' => $data,
        ]);
    }
}
