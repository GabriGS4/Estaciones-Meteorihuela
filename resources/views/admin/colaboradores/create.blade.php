@extends('admin.layouts.app')

@section('title', 'Nuevo colaborador')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sponsors.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver a colaboradores</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-2">Nuevo colaborador</h2>
</div>

<div class="bg-white rounded-xl border border-gray-200 max-w-lg">
    <form method="POST" action="{{ route('admin.sponsors.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">URL del colaborador</label>
            <input type="url" name="link_url" value="{{ old('link_url') }}" placeholder="https://..."
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('link_url') border-red-400 @enderror">
            @error('link_url')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
            <input type="file" name="logo" accept="image/*"
                   class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="mt-1 text-xs text-gray-400">JPG, PNG, GIF o WebP. Máx. 2MB.</p>
            @error('logo')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" id="active" value="1" checked
                   class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="active" class="text-sm font-medium text-gray-700">Activo (visible en la app)</label>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                Crear colaborador
            </button>
            <a href="{{ route('admin.sponsors.index') }}"
               class="px-5 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
