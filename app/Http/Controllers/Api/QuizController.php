<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use App\Models\QuizRanking;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Obtener todas las preguntas de forma aleatoria
     */
    public function getQuestions()
    {
        $allQuestions = QuizQuestion::all();

        $sponsor = $allQuestions->where('es_patrocinador', true);

        $regular = $allQuestions
            ->where('es_patrocinador', false)
            ->shuffle()
            ->take(20 - $sponsor->count());

        $questions = $sponsor
            ->concat($regular)
            ->shuffle()
            ->values();

        return response()->json($questions);
    }
    /**
     * Guardar el resultado de la partida
     */
    public function saveRanking(Request $request)
    {
        $validated = $request->validate([
            'nombre_jugador' => 'required|string|max:255',
            'puntos' => 'required|integer|min:0',
            'total_preguntas' => 'required|integer|min:1',
            'porcentaje' => 'required|numeric|min:0|max:100',
            'tiempo' => 'required|integer|min:0',
        ]);

        $ranking = QuizRanking::updateOrCreate(
            ['nombre_jugador' => $validated['nombre_jugador']],
            [
                'puntos' => $validated['puntos'],
                'total_preguntas' => $validated['total_preguntas'],
                'porcentaje' => $validated['porcentaje'],
                'tiempo' => $validated['tiempo'],
            ]
        );

        return response()->json([
            'message' => 'Ranking guardado correctamente.',
            'data' => $ranking
        ], 201);
    }

    /**
     * Obtener el top 50 global del ranking
     */
    public function getRanking()
    {
        $rankings = QuizRanking::orderByDesc('puntos')
            ->orderBy('tiempo')
            ->take(10)
            ->get();

        return response()->json($rankings);
    }
}
