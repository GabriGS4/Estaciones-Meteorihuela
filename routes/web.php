<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\WeatherApiController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function() {
    return response()->json(['ok' => true]);
});


// Página embebible del mapa
