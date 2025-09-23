<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\CurrentObservation;
use App\Models\DailySummary;
use App\Models\HourlyObservation;

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
}
