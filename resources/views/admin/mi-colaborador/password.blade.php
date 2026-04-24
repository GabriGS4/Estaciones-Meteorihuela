@extends('admin.layouts.app')

@section('title', 'Cambiar contraseña')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.mi-colaborador.show') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver a mi colaborador</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-2">Cambiar contraseña</h2>
</div>

<div class="bg-white rounded-xl border border-gray-200 max-w-lg">
    <form method="POST" action="{{ route('admin.mi-colaborador.password.update') }}" class="p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
            <input type="password" name="current_password" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('current_password') border-red-400 @enderror">
            @error('current_password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
            <input type="password" name="password_confirmation" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                Cambiar contraseña
            </button>
            <a href="{{ route('admin.mi-colaborador.show') }}"
               class="px-5 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
