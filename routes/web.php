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
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\Admin\SponsorStoryController;
use App\Http\Controllers\Admin\MiColaboradorController;

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

    // Admin-only routes
    Route::middleware(['auth', 'admin'])->group(function () {
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

        // Participants
        Route::prefix('participantes')->name('participants.')->group(function () {
            Route::get('/',              [ParticipantController::class, 'index'])->name('index');
            Route::get('/datos',         [ParticipantController::class, 'data'])->name('data');
            Route::get('/{participant}/editar', [ParticipantController::class, 'edit'])->name('edit');
            Route::put('/{participant}', [ParticipantController::class, 'update'])->name('update');
            Route::delete('/borrar-todos', [ParticipantController::class, 'destroyAll'])->name('destroy-all');
            Route::delete('/bulk',       [ParticipantController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::delete('/{participant}', [ParticipantController::class, 'destroy'])->name('destroy');
        });

        // Users
        Route::prefix('usuarios')->name('users.')->group(function () {
            Route::get('/',             [UserController::class, 'index'])->name('index');
            Route::get('/datos',        [UserController::class, 'data'])->name('data');
            Route::get('/nuevo',        [UserController::class, 'create'])->name('create');
            Route::post('/',            [UserController::class, 'store'])->name('store');
            Route::get('/{user}/editar', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',       [UserController::class, 'update'])->name('update');
            Route::delete('/{user}',    [UserController::class, 'destroy'])->name('destroy');
        });

        // Settings
        Route::prefix('configuracion')->name('settings.')->group(function () {
            Route::get('/lavadero',    [SettingsController::class, 'carWash'])->name('car-wash');
            Route::put('/lavadero',    [SettingsController::class, 'updateCarWash'])->name('car-wash.update');
            Route::get('/playas',      [SettingsController::class, 'beaches'])->name('beaches');
            Route::put('/playas',      [SettingsController::class, 'updateBeaches'])->name('beaches.update');
            Route::get('/quiz',        [SettingsController::class, 'quiz'])->name('quiz');
            Route::put('/quiz',        [SettingsController::class, 'updateQuiz'])->name('quiz.update');
        });

        // Colaboradores (sponsors) CRUD
        Route::prefix('colaboradores')->name('sponsors.')->group(function () {
            Route::get('/',              [SponsorController::class, 'index'])->name('index');
            Route::get('/nuevo',         [SponsorController::class, 'create'])->name('create');
            Route::post('/',             [SponsorController::class, 'store'])->name('store');
            Route::get('/{sponsor}/editar', [SponsorController::class, 'edit'])->name('edit');
            Route::put('/{sponsor}',     [SponsorController::class, 'update'])->name('update');
            Route::delete('/{sponsor}',  [SponsorController::class, 'destroy'])->name('destroy');

            // Stories de un colaborador
            Route::post('/{sponsor}/stories',       [SponsorStoryController::class, 'store'])->name('stories.store');
        });

        // Stories actions (admin)
        Route::prefix('stories')->name('stories.')->group(function () {
            Route::delete('/{story}', [SponsorStoryController::class, 'destroy'])->name('destroy');
            Route::patch('/{story}/toggle', [SponsorStoryController::class, 'toggle'])->name('toggle');
            Route::patch('/{story}/extra-views', [SponsorStoryController::class, 'updateExtraViews'])->name('update-extra-views');
        });
    });

    // Sponsor-user-only routes
    Route::middleware(['auth', 'sponsor.user'])->prefix('mi-colaborador')->name('mi-colaborador.')->group(function () {
        Route::get('/',              [MiColaboradorController::class, 'show'])->name('show');
        Route::put('/',              [MiColaboradorController::class, 'update'])->name('update');
        Route::get('/password',      [MiColaboradorController::class, 'password'])->name('password');
        Route::put('/password',      [MiColaboradorController::class, 'updatePassword'])->name('password.update');
        Route::post('/stories',      [MiColaboradorController::class, 'storeStory'])->name('stories.store');
        Route::delete('/stories/{story}', [MiColaboradorController::class, 'destroyStory'])->name('stories.destroy');
        Route::patch('/stories/{story}/toggle', [MiColaboradorController::class, 'toggleStory'])->name('stories.toggle');
    });
});

// Patrocinadores - Rutas de análisis públicas
Route::prefix('patrocinadores')->group(function () {
    Route::get('/',        [SponsorReportController::class, 'index'])->name('sponsors.report');
    Route::get('/pdf',     [SponsorReportController::class, 'pdf'])->name('sponsors.pdf');
    Route::get('/{id}/pdf', [SponsorReportController::class, 'pdfSponsor'])->name('sponsors.pdf.single')
        ->where('id', '[0-9]+');
});
