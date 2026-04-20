@extends('admin.layouts.app')

@section('title', 'Patrocinador Lavadero')

@section('content')
<div class="max-w-xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Lavadero de coche</h2>
        <p class="text-gray-500 text-sm mt-1">Configuración del patrocinador</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.settings.car-wash.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del patrocinador</label>
                    <input type="text" name="CAR_WASH_SPONSOR_NAME"
                           value="{{ old('CAR_WASH_SPONSOR_NAME', $values['CAR_WASH_SPONSOR_NAME']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('CAR_WASH_SPONSOR_NAME') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL del logo</label>
                    <input type="url" name="CAR_WASH_SPONSOR_LOGO_URL"
                           value="{{ old('CAR_WASH_SPONSOR_LOGO_URL', $values['CAR_WASH_SPONSOR_LOGO_URL']) }}"
                           placeholder="https://..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('CAR_WASH_SPONSOR_LOGO_URL') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @if($values['CAR_WASH_SPONSOR_LOGO_URL'])
                        <img src="{{ $values['CAR_WASH_SPONSOR_LOGO_URL'] }}" alt="Logo preview"
                             class="mt-2 h-12 rounded border border-gray-200 object-contain">
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL de destino (clic en logo)</label>
                    <input type="url" name="CAR_WASH_SPONSOR_LINK_URL"
                           value="{{ old('CAR_WASH_SPONSOR_LINK_URL', $values['CAR_WASH_SPONSOR_LINK_URL']) }}"
                           placeholder="https://..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('CAR_WASH_SPONSOR_LINK_URL') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Color override</label>
                    <select name="CAR_WASH_OVERRIDE_COLOR"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="" {{ old('CAR_WASH_OVERRIDE_COLOR', $values['CAR_WASH_OVERRIDE_COLOR']) === '' ? 'selected' : '' }}>
                            Sin override (usa el tiempo real)
                        </option>
                        <option value="green" {{ old('CAR_WASH_OVERRIDE_COLOR', $values['CAR_WASH_OVERRIDE_COLOR']) === 'green' ? 'selected' : '' }}>
                            🟢 Verde (día perfecto para lavar)
                        </option>
                        <option value="yellow" {{ old('CAR_WASH_OVERRIDE_COLOR', $values['CAR_WASH_OVERRIDE_COLOR']) === 'yellow' ? 'selected' : '' }}>
                            🟡 Amarillo (condiciones aceptables)
                        </option>
                        <option value="red" {{ old('CAR_WASH_OVERRIDE_COLOR', $values['CAR_WASH_OVERRIDE_COLOR']) === 'red' ? 'selected' : '' }}>
                            🔴 Rojo (no recomendado)
                        </option>
                    </select>
                    @error('CAR_WASH_OVERRIDE_COLOR') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
