<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizQuestionController extends Controller
{
    public function index()
    {
        return view('admin.questions.index');
    }

    public function data()
    {
        $questions = QuizQuestion::select([
            'id', 'pregunta', 'opcion_a', 'opcion_b', 'opcion_c', 'opcion_d',
            'respuesta_correcta', 'es_patrocinador', 'imagen', 'created_at',
        ])->get();

        return response()->json(['data' => $questions]);
    }

    public function create()
    {
        return view('admin.questions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pregunta'           => 'required|string|max:1000',
            'opcion_a'           => 'required|string|max:500',
            'opcion_b'           => 'required|string|max:500',
            'opcion_c'           => 'required|string|max:500',
            'opcion_d'           => 'required|string|max:500',
            'respuesta_correcta' => 'required|in:a,b,c,d',
            'es_patrocinador'    => 'boolean',
            'imagen'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['es_patrocinador'] = $request->boolean('es_patrocinador');

        if ($request->hasFile('imagen')) {
            $validated['imagen'] = $request->file('imagen')->store('quiz/imagenes', 'public');
        }

        QuizQuestion::create($validated);

        return redirect()->route('admin.questions.index')->with('success', 'Pregunta creada correctamente.');
    }

    public function edit(QuizQuestion $question)
    {
        return view('admin.questions.edit', compact('question'));
    }

    public function update(Request $request, QuizQuestion $question)
    {
        $validated = $request->validate([
            'pregunta'           => 'required|string|max:1000',
            'opcion_a'           => 'required|string|max:500',
            'opcion_b'           => 'required|string|max:500',
            'opcion_c'           => 'required|string|max:500',
            'opcion_d'           => 'required|string|max:500',
            'respuesta_correcta' => 'required|in:a,b,c,d',
            'es_patrocinador'    => 'boolean',
            'imagen'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $validated['es_patrocinador'] = $request->boolean('es_patrocinador');

        if ($request->hasFile('imagen')) {
            if ($question->imagen) {
                Storage::disk('public')->delete($question->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('quiz/imagenes', 'public');
        } elseif ($request->boolean('eliminar_imagen') && $question->imagen) {
            Storage::disk('public')->delete($question->imagen);
            $validated['imagen'] = null;
        } else {
            unset($validated['imagen']);
        }

        $question->update($validated);

        return redirect()->route('admin.questions.index')->with('success', 'Pregunta actualizada correctamente.');
    }

    public function destroy(QuizQuestion $question)
    {
        if ($question->imagen) {
            Storage::disk('public')->delete($question->imagen);
        }
        $question->delete();
        return response()->json(['success' => true]);
    }

    public function importForm()
    {
        return view('admin.questions.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_file' => 'required_without:json_text|file|mimes:json,txt',
            'json_text' => 'required_without:json_file|nullable|string',
        ]);

        $jsonContent = $request->hasFile('json_file')
            ? file_get_contents($request->file('json_file')->getRealPath())
            : $request->input('json_text');

        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json' => 'JSON inválido: ' . json_last_error_msg()]);
        }

        if (isset($data['questions'])) {
            $data = $data['questions'];
        }

        if (!is_array($data)) {
            return back()->withErrors(['json' => 'El JSON debe ser un array de preguntas.']);
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($data as $item) {
            $required = ['pregunta', 'opcion_a', 'opcion_b', 'opcion_c', 'opcion_d', 'respuesta_correcta'];
            $missing  = array_diff($required, array_keys($item));

            if (!empty($missing)) {
                $skipped++;
                continue;
            }

            if (!in_array(strtolower($item['respuesta_correcta']), ['a', 'b', 'c', 'd'])) {
                $skipped++;
                continue;
            }

            QuizQuestion::create([
                'pregunta'           => $item['pregunta'],
                'opcion_a'           => $item['opcion_a'],
                'opcion_b'           => $item['opcion_b'],
                'opcion_c'           => $item['opcion_c'],
                'opcion_d'           => $item['opcion_d'],
                'respuesta_correcta' => strtolower($item['respuesta_correcta']),
                'es_patrocinador'    => (bool) ($item['es_patrocinador'] ?? false),
            ]);

            $imported++;
        }

        return redirect()->route('admin.questions.index')
            ->with('success', "Importadas {$imported} preguntas. Omitidas: {$skipped}.");
    }
}
