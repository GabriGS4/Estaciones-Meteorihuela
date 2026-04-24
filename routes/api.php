<?php

use App\Http\Controllers\Api\BeachesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarWashController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\SponsorController;
use App\Http\Controllers\Api\QuizSponsorController;

Route::get('/quiz/questions', [QuizController::class, 'getQuestions']);
Route::post('/quiz/ranking', [QuizController::class, 'saveRanking']);
Route::get('/quiz/ranking', [QuizController::class, 'getRanking']);

// Car Wash widget
Route::get('/car-wash/sponsor', [CarWashController::class, 'sponsor']);

// Beach Sponsor Widget
Route::get('/beach/sponsor', [BeachesController::class, 'sponsor']);

// Quiz Sponsor Widget
Route::get('/quiz/sponsor', [QuizSponsorController::class, 'sponsor']);

Route::get('/app/version', function () {
    return response()->json(['version' => config('app.version')]);
});

// Sponsor routes — POST only (consumed by the mobile app)
Route::prefix('sponsors')->group(function () {
    Route::post('/sync',             [SponsorController::class, 'sync']);        // upsert sponsors+stories
    Route::post('/list',             [SponsorController::class, 'list']);
    Route::post('/track-story-view', [SponsorController::class, 'trackStoryView']);
    Route::post('/track-link-click', [SponsorController::class, 'trackLinkClick']);
});
