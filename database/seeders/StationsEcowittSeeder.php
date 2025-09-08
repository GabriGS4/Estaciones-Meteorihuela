<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class StationsEcowittSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        Station::where('api_key', 'Ecowitt')->delete();
        // Hacemos petición a la API de Ecowitt para obtener las estaciones
        $applicationKeys = explode(',', env('APPLICATION_KEY_ECOWITT'));
        $apiKeys = explode(',', env('API_KEY_ECOWITT'));
        // Por simplicidad, usamos la primera clave de cada una
        $apiKey = $apiKeys[0];
        $applicationKey = $applicationKeys[1];
        $url = "https://api.ecowitt.net/api/v3/device/list?application_key=" . $applicationKey . "&api_key=" . $apiKey . "&limit=50";

        try {
            $response = Http::get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['code'] === 0 && isset($data['data']['list'])) {
                    foreach ($data['data']['list'] as $stationData) {
                        Station::create([
                            'station_id' => null, // Como solicitas, dejamos station_id como null
                            'api_key' => $stationData['mac'], // La MAC es la api_key
                            'name' => $stationData['name'],
                            'country' => 'ES', // Asumiendo que son estaciones españolas
                            'lat' => $stationData['latitude'],
                            'lon' => $stationData['longitude'],
                            'elevation' => null, // No viene en los datos de ejemplo
                            'use_ecowitt' => true,
                        ]);
                    }
                    
                    $this->command->info('Se han creado ' . count($data['data']['list']) . ' estaciones de Ecowitt');
                } else {
                    $this->command->error('Error en la respuesta de la API: ' . $data['msg']);
                }
            } else {
                $this->command->error('Error en la petición HTTP: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->command->error('Error al hacer la petición: ' . $e->getMessage());
        }
    }
}
