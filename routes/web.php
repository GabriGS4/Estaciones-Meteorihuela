<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function() {
    return response()->json(['ok' => true]);
});


Route::get('/migrate-now', function() {
    // Esto solo debe estar activo temporalmente
    Artisan::call('migrate', ['--force' => true]);

    // Ejecutar seeders
    Artisan::call('db:seed', ['--force' => true]);

    return 'Migraciones y seeders ejecutados ✅';
});