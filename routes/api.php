<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function() {
    return response()->json(['ok' => true]);
});


// Rutas API para datos meteorológicos
Route::prefix('weather')->group(function () {
    // Listar estaciones disponibles
    Route::get('/stations', [WeatherApiController::class, 'stations']);
    
    // Datos meteorológicos generales
    Route::get('/current', [WeatherApiController::class, 'current']);
    Route::get('/daily', [WeatherApiController::class, 'daily']);
    Route::get('/hourly', [WeatherApiController::class, 'hourly']);
    
    // Rutas específicas por estación
    Route::get('/current/{station}', [WeatherApiController::class, 'currentByStation']);
    Route::get('/daily/{station}', [WeatherApiController::class, 'dailyByStation']);
    Route::get('/hourly/{station}', [WeatherApiController::class, 'hourlyByStation']);
})->middleware(\App\Http\Middleware\ValidateApiPassword::class);
