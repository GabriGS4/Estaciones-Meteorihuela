<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuizController;

Route::get('/quiz/questions', [QuizController::class, 'getQuestions']);
Route::post('/quiz/ranking', [QuizController::class, 'saveRanking']);
Route::get('/quiz/ranking', [QuizController::class, 'getRanking']);
