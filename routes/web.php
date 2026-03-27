<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\WeatherApiController;
use App\Http\Controllers\Web\SponsorReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function() {
    return response()->json(['ok' => true]);
});


// Página embebible del mapa

// Patrocinadores - Rutas de análisis públicas
Route::prefix('patrocinadores')->group(function () {
    Route::get('/',        [SponsorReportController::class, 'index'])->name('sponsors.report');
    Route::get('/pdf',     [SponsorReportController::class, 'pdf'])->name('sponsors.pdf');
    Route::get('/{id}/pdf', [SponsorReportController::class, 'pdfSponsor'])->name('sponsors.pdf.single')
        ->where('id', '[0-9]+');
});
