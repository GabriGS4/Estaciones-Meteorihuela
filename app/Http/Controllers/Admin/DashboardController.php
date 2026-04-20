<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\QuizRanking;

class DashboardController extends Controller
{
    public function index()
    {
        $totalQuestions  = QuizQuestion::count();
        $sponsorQuestions = QuizQuestion::where('es_patrocinador', true)->count();
        $totalRankings   = QuizRanking::count();

        return view('admin.dashboard', compact('totalQuestions', 'sponsorQuestions', 'totalRankings'));
    }
}
