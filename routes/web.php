<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function() {
    return response()->json(['ok' => true]);
});

Route::get('/run-fetch-weather', function(Request $request) {
    $token = $request->query('token');
    if ($token !== env('CRON_TOKEN','miclavetemporal123')) {
        abort(403, 'No autorizado');
    }

    // Ejecuta tu comando
    Artisan::call('app:fetch-weather-data');

    return 'Comando ejecutado ✅';
});