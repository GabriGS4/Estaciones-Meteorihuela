<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizRanking;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index()
    {
        return view('admin.participants.index');
    }

    public function data()
    {
        $participants = QuizRanking::select([
            'id', 'nombre_jugador', 'puntos', 'total_preguntas', 'porcentaje', 'tiempo', 'created_at',
        ])->get();

        return response()->json(['data' => $participants]);
    }

    public function edit(QuizRanking $participant)
    {
        return view('admin.participants.edit', compact('participant'));
    }

    public function update(Request $request, QuizRanking $participant)
    {
        $validated = $request->validate([
            'nombre_jugador'  => 'required|string|max:255',
            'puntos'          => 'required|integer|min:0',
            'total_preguntas' => 'required|integer|min:1',
            'porcentaje'      => 'required|numeric|min:0|max:100',
            'tiempo'          => 'nullable|integer|min:0',
        ]);

        $participant->update($validated);

        return redirect()->route('admin.participants.index')
            ->with('success', 'Participante actualizado correctamente.');
    }

    public function destroy(QuizRanking $participant)
    {
        $participant->delete();
        return response()->json(['success' => true]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:quiz_rankings,id',
        ]);

        QuizRanking::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true, 'deleted' => count($request->ids)]);
    }

    public function destroyAll()
    {
        $count = QuizRanking::count();
        QuizRanking::truncate();

        return response()->json(['success' => true, 'deleted' => $count]);
    }
}
