@extends('admin.layouts.app')

@section('title', 'Mi colaborador')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Mi colaborador</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona tu información y contenido</p>
    </div>
    <a href="{{ route('admin.mi-colaborador.password') }}"
       class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-colors">
        Cambiar contraseña
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Datos del colaborador --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Mis datos</h3>
        </div>
        <form method="POST" action="{{ route('admin.mi-colaborador.update') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $sponsor->name) }}" required
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL de mi web</label>
                <input type="url" name="link_url" value="{{ old('link_url', $sponsor->getRawOriginal('link_url')) }}" placeholder="https://..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('link_url') border-red-400 @enderror">
                @error('link_url')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logo actual</label>
                @if($sponsor->logo_url)
                    <img src="{{ $sponsor->logo_url }}" alt="{{ $sponsor->name }}"
                         class="w-20 h-20 rounded-xl object-contain bg-gray-100 border border-gray-200 mb-3">
                @else
                    <div class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center text-gray-400 text-sm mb-3">Sin logo</div>
                @endif
                <input type="file" name="logo" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="mt-1 text-xs text-gray-400">JPG, PNG, GIF o WebP. Máx. 2MB. Reemplazará el logo actual.</p>
                @error('logo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                Guardar cambios
            </button>
        </form>
    </div>

    {{-- Gestión de stories --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Mi contenido</h3>
            <p class="text-xs text-gray-400 mt-0.5">Imágenes y vídeos que se muestran en la app</p>
        </div>

        {{-- Upload form --}}
        <div class="p-6 border-b border-gray-100">
            <form method="POST" action="{{ route('admin.mi-colaborador.stories.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subir nuevo contenido</label>
                    <input type="file" name="media" accept="image/*,video/*" required
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="mt-1 text-xs text-gray-400">Imagen o vídeo. Máx. 100MB.</p>
                    @error('media')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Subir contenido
                </button>
            </form>
        </div>

        {{-- Stories list --}}
        <div class="p-6">
            @if($sponsor->stories->isEmpty())
                <p class="text-sm text-gray-400 text-center py-4">Sin contenido todavía</p>
            @else
                <div class="space-y-3">
                    @foreach($sponsor->stories as $story)
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50">
                        <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                            @if($story->media_type === 'video')
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @else
                                <img src="{{ $story->media_url }}" alt="Story" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-700 uppercase tracking-wide">{{ $story->media_type }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $story->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="toggleStory({{ $story->id }}, this)"
                                    class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $story->active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $story->active ? 'Activa' : 'Inactiva' }}
                            </button>
                            <button onclick="deleteStory({{ $story->id }})"
                                    class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function toggleStory(id, btn) {
        fetch(`/admin/mi-colaborador/stories/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                btn.textContent = data.active ? 'Activa' : 'Inactiva';
                btn.className = data.active
                    ? 'px-2.5 py-1 rounded-lg text-xs font-medium transition-colors bg-green-100 text-green-700 hover:bg-green-200'
                    : 'px-2.5 py-1 rounded-lg text-xs font-medium transition-colors bg-gray-100 text-gray-500 hover:bg-gray-200';
            }
        });
    }

    function deleteStory(id) {
        if (!confirm('¿Eliminar este contenido? No se puede deshacer.')) return;
        fetch(`/admin/mi-colaborador/stories/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
        });
    }
</script>
@endpush
@endsection
