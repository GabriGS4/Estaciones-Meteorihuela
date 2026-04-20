<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\WeatherApiController;
use App\Http\Controllers\Web\SponsorReportController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\QuizQuestionController;
use App\Http\Controllers\Admin\SettingsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function() {
    return response()->json(['ok' => true]);
});


// Admin panel
Route::prefix('admin')->name('admin.')->group(function () {
    // Auth (guest only)
    Route::middleware('guest')->group(function () {
        Route::get('login',  [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.post');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    // Protected routes
    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Questions
        Route::prefix('preguntas')->name('questions.')->group(function () {
            Route::get('/',                [QuizQuestionController::class, 'index'])->name('index');
            Route::get('/datos',           [QuizQuestionController::class, 'data'])->name('data');
            Route::get('/nueva',           [QuizQuestionController::class, 'create'])->name('create');
            Route::post('/',               [QuizQuestionController::class, 'store'])->name('store');
            Route::get('/{question}/editar', [QuizQuestionController::class, 'edit'])->name('edit');
            Route::put('/{question}',      [QuizQuestionController::class, 'update'])->name('update');
            Route::delete('/{question}',   [QuizQuestionController::class, 'destroy'])->name('destroy');
            Route::get('/importar',        [QuizQuestionController::class, 'importForm'])->name('import');
            Route::post('/importar',       [QuizQuestionController::class, 'import'])->name('import.post');
        });

        // Settings
        Route::prefix('configuracion')->name('settings.')->group(function () {
            Route::get('/lavadero',    [SettingsController::class, 'carWash'])->name('car-wash');
            Route::put('/lavadero',    [SettingsController::class, 'updateCarWash'])->name('car-wash.update');
            Route::get('/playas',      [SettingsController::class, 'beaches'])->name('beaches');
            Route::put('/playas',      [SettingsController::class, 'updateBeaches'])->name('beaches.update');
        });
    });
});

// Página embebible del mapa

// Patrocinadores - Rutas de análisis públicas
Route::prefix('patrocinadores')->group(function () {
    Route::get('/',        [SponsorReportController::class, 'index'])->name('sponsors.report');
    Route::get('/pdf',     [SponsorReportController::class, 'pdf'])->name('sponsors.pdf');
    Route::get('/{id}/pdf', [SponsorReportController::class, 'pdfSponsor'])->name('sponsors.pdf.single')
        ->where('id', '[0-9]+');
});
