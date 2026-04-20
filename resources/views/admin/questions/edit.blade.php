@extends('admin.layouts.app')

@section('title', 'Editar pregunta')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.questions.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Editar pregunta #{{ $question->id }}</h2>
            <p class="text-gray-500 text-sm mt-0.5">Modificar pregunta del quiz</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.questions.update', $question) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.questions._form', ['question' => $question])
            <div class="flex gap-3 pt-6 mt-6 border-t border-gray-100">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Guardar cambios
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
