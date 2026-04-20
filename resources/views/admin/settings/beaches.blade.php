@extends('admin.layouts.app')

@section('title', 'Patrocinador Playas')

@section('content')
<div class="max-w-xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Playas</h2>
        <p class="text-gray-500 text-sm mt-1">Configuración del patrocinador</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.beaches.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del patrocinador</label>
                    <input type="text" name="BEACH_SPONSOR_NAME"
                           value="{{ old('BEACH_SPONSOR_NAME', $values['BEACH_SPONSOR_NAME']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('BEACH_SPONSOR_NAME') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL del logo</label>
                    <input type="url" name="BEACH_SPONSOR_LOGO_URL"
                           value="{{ old('BEACH_SPONSOR_LOGO_URL', $values['BEACH_SPONSOR_LOGO_URL']) }}"
                           placeholder="https://..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('BEACH_SPONSOR_LOGO_URL') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @if($values['BEACH_SPONSOR_LOGO_URL'])
                        <img src="{{ $values['BEACH_SPONSOR_LOGO_URL'] }}" alt="Logo preview"
                             class="mt-2 h-12 rounded border border-gray-200 object-contain">
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL de destino (clic en logo)</label>
                    <input type="url" name="BEACH_SPONSOR_LINK_URL"
                           value="{{ old('BEACH_SPONSOR_LINK_URL', $values['BEACH_SPONSOR_LINK_URL']) }}"
                           placeholder="https://..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('BEACH_SPONSOR_LINK_URL') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 pt-6 mt-6 border-t border-gray-100">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
