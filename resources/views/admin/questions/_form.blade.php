@php $question = $question ?? null; @endphp

<div class="space-y-5">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Pregunta <span class="text-red-500">*</span></label>
        <textarea name="pregunta" rows="3" required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pregunta') border-red-400 @enderror">{{ old('pregunta', $question?->pregunta) }}</textarea>
        @error('pregunta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['a', 'b', 'c', 'd'] as $opt)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Opción {{ strtoupper($opt) }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="opcion_{{ $opt }}" required
                   value="{{ old('opcion_'.$opt, $question?->{'opcion_'.$opt}) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('opcion_'.$opt) border-red-400 @enderror">
            @error('opcion_'.$opt) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @endforeach
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Respuesta correcta <span class="text-red-500">*</span></label>
        <select name="respuesta_correcta" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('respuesta_correcta') border-red-400 @enderror">
            @foreach(['a', 'b', 'c', 'd'] as $opt)
            <option value="{{ $opt }}" {{ old('respuesta_correcta', $question?->respuesta_correcta) === $opt ? 'selected' : '' }}>
                Opción {{ strtoupper($opt) }}
            </option>
            @endforeach
        </select>
        @error('respuesta_correcta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Imagen --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Imagen <span class="text-gray-400 font-normal">(opcional · jpg, jpeg, png · máx. 2 MB)</span></label>

        @if($question?->imagen)
            <div class="mb-3 flex items-start gap-4">
                <img src="{{ Storage::url($question->imagen) }}"
                     alt="Imagen actual"
                     class="h-24 w-auto rounded-lg border border-gray-200 object-cover"
                     id="imagen-preview">
                <div class="flex flex-col gap-2">
                    <p class="text-xs text-gray-500">Imagen actual</p>
                    <label class="flex items-center gap-2 text-xs text-red-600 cursor-pointer">
                        <input type="checkbox" name="eliminar_imagen" value="1" class="rounded border-gray-300">
                        Eliminar imagen
                    </label>
                </div>
            </div>
        @else
            <img id="imagen-preview" src="" alt="" class="hidden mb-3 h-24 w-auto rounded-lg border border-gray-200 object-cover">
        @endif

        <input type="file" name="imagen" id="imagen-input"
               accept=".jpg,.jpeg,.png"
               class="block w-full text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-lg file:border-0
                      file:text-sm file:font-medium
                      file:bg-blue-50 file:text-blue-700
                      hover:file:bg-blue-100
                      @error('imagen') border border-red-400 rounded-lg @enderror">
        @error('imagen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3">
        <input type="hidden" name="es_patrocinador" value="0">
        <input type="checkbox" id="es_patrocinador" name="es_patrocinador" value="1"
               {{ old('es_patrocinador', $question?->es_patrocinador) ? 'checked' : '' }}
               class="rounded border-gray-300 text-blue-600">
        <label for="es_patrocinador" class="text-sm font-medium text-gray-700">
            Pregunta de patrocinador ⭐
        </label>
    </div>
</div>

<script>
document.getElementById('imagen-input')?.addEventListener('change', function () {
    const preview = document.getElementById('imagen-preview');
    if (this.files && this.files[0]) {
        preview.src = URL.createObjectURL(this.files[0]);
        preview.classList.remove('hidden');
    }
});
</script>
