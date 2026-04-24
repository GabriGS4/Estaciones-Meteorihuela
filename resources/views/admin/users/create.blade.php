@extends('admin.layouts.app')

@section('title', 'Nuevo usuario')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Volver a usuarios</a>
    <h2 class="text-2xl font-bold text-gray-900 mt-2">Nuevo usuario</h2>
</div>

<div class="bg-white rounded-xl border border-gray-200 max-w-lg">
    <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-5" id="userForm">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
            @error('password')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input type="password" name="password_confirmation" required
                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 space-y-3">
            <p class="text-sm font-medium text-gray-700">Rol y acceso</p>

            <div class="flex items-center gap-3">
                <input type="hidden" name="is_admin" value="0">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                       onchange="toggleSponsorField(this)">
                <label for="is_admin" class="text-sm text-gray-700">Administrador (acceso total al panel)</label>
            </div>

            <div id="sponsorField" class="{{ old('is_admin') ? 'hidden' : '' }}">
                <label class="block text-sm font-medium text-gray-700 mb-1">Colaborador asociado <span class="text-gray-400 font-normal">(opcional)</span></label>
                <select name="sponsor_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('sponsor_id') border-red-400 @enderror">
                    <option value="">— Sin colaborador —</option>
                    @foreach($sponsors as $sponsor)
                        <option value="{{ $sponsor->id }}" {{ old('sponsor_id') == $sponsor->id ? 'selected' : '' }}>
                            {{ $sponsor->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Si se asigna un colaborador, el usuario solo podrá gestionar ese colaborador.</p>
                @error('sponsor_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                Crear usuario
            </button>
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toggleSponsorField(checkbox) {
        document.getElementById('sponsorField').classList.toggle('hidden', checkbox.checked);
    }
</script>
@endpush
@endsection
