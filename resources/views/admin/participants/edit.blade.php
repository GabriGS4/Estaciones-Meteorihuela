@extends('admin.layouts.app')

@section('title', 'Editar participante')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.participants.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Volver al listado
    </a>
</div>

<div class="max-w-xl">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Editar participante</h2>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.participants.update', $participant) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del jugador</label>
                    <input type="text" name="nombre_jugador"
                           value="{{ old('nombre_jugador', $participant->nombre_jugador) }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nombre_jugador') border-red-400 @enderror">
                    @error('nombre_jugador')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Puntos</label>
                        <input type="number" name="puntos" min="0"
                               value="{{ old('puntos', $participant->puntos) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('puntos') border-red-400 @enderror">
                        @error('puntos')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total preguntas</label>
                        <input type="number" name="total_preguntas" min="1"
                               value="{{ old('total_preguntas', $participant->total_preguntas) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('total_preguntas') border-red-400 @enderror">
                        @error('total_preguntas')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Porcentaje (%)</label>
                        <input type="number" name="porcentaje" min="0" max="100" step="0.01"
                               value="{{ old('porcentaje', $participant->porcentaje) }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('porcentaje') border-red-400 @enderror">
                        @error('porcentaje')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tiempo (segundos)</label>
                        <input type="number" name="tiempo" min="0"
                               value="{{ old('tiempo', $participant->tiempo) }}"
                               placeholder="Opcional"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('tiempo') border-red-400 @enderror">
                        @error('tiempo')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Guardar cambios
                </button>
                <a href="{{ route('admin.participants.index') }}"
                   class="px-5 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-medium transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
