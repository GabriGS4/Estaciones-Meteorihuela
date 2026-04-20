@extends('admin.layouts.app')

@section('title', 'Importar preguntas')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.questions.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Importar preguntas</h2>
            <p class="text-gray-500 text-sm mt-0.5">Carga masiva mediante JSON</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        <form method="POST" action="{{ route('admin.questions.import.post') }}" enctype="multipart/form-data">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Archivo JSON</label>
                <input type="file" name="json_file" accept=".json,.txt"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1">O pega el JSON directamente abajo</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">JSON (texto)</label>
                <textarea name="json_text" rows="10" placeholder='[&#10;  {&#10;    "pregunta": "¿...?",&#10;    "opcion_a": "...",&#10;    "opcion_b": "...",&#10;    "opcion_c": "...",&#10;    "opcion_d": "...",&#10;    "respuesta_correcta": "a",&#10;    "es_patrocinador": false&#10;  }&#10;]'
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-xs font-semibold text-gray-600 mb-2">Formato esperado:</p>
                <pre class="text-xs text-gray-500 overflow-auto">[
  {
    "pregunta": "¿Cuál es la capital de España?",
    "opcion_a": "Madrid",
    "opcion_b": "Barcelona",
    "opcion_c": "Valencia",
    "opcion_d": "Sevilla",
    "respuesta_correcta": "a",
    "es_patrocinador": false
  }
]</pre>
                <p class="text-xs text-gray-400 mt-2">También acepta <code class="bg-gray-200 px-1 rounded">{"questions": [...]}</code></p>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Importar
                </button>
                <a href="{{ route('admin.questions.index') }}"
                   class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
