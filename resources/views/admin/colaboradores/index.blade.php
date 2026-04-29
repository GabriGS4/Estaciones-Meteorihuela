@extends('admin.layouts.app')

@section('title', 'Colaboradores')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Colaboradores</h2>
        <p class="text-sm text-gray-500 mt-1">Gestiona los patrocinadores y su contenido</p>
    </div>
    <a href="{{ route('admin.sponsors.create') }}"
       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
        + Nuevo colaborador
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden overflow-x-auto">
    <table id="sponsorsTable" class="w-full text-sm min-w-[600px]">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Logo</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Nombre</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">URL</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Stories</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($sponsors as $sponsor)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    @if($sponsor->logo_url)
                        <img src="{{ $sponsor->logo_url }}" alt="{{ $sponsor->name }}"
                             class="w-10 h-10 rounded-full object-contain bg-gray-100 border border-gray-200">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 text-xs font-bold">
                            {{ strtoupper(substr($sponsor->name, 0, 2)) }}
                        </div>
                    @endif
                </td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $sponsor->name }}</td>
                <td class="px-4 py-3 text-gray-500">
                    @if($sponsor->link_url)
                        <a href="{{ $sponsor->link_url }}" target="_blank"
                           class="text-blue-600 hover:underline truncate block max-w-[200px]">
                            {{ $sponsor->link_url }}
                        </a>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 text-xs font-bold text-gray-700">
                        {{ $sponsor->stories_count }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($sponsor->active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactivo
                        </span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('admin.sponsors.edit', $sponsor) }}"
                           class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-blue-200">
                            Editar
                        </a>
                        <button onclick="deleteSponsor({{ $sponsor->id }}, '{{ addslashes($sponsor->name) }}')"
                                class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-red-200">
                            Eliminar
                        </button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Delete modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Eliminar colaborador</h3>
        <p class="text-sm text-gray-600 mb-4">¿Eliminar <strong id="sponsorName"></strong>? Se borrarán también todas sus stories y archivos. Esta acción no se puede deshacer.</p>
        <div class="flex gap-3">
            <button onclick="confirmDelete()"
                    class="flex-1 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                Eliminar
            </button>
            <button onclick="closeModal()"
                    class="flex-1 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let pendingId = null;

    function deleteSponsor(id, name) {
        pendingId = id;
        document.getElementById('sponsorName').textContent = name;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        pendingId = null;
    }

    function confirmDelete() {
        if (!pendingId) return;
        fetch(`/admin/colaboradores/${pendingId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    new DataTable('#sponsorsTable', {
        language: {
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ entradas',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
            infoEmpty: 'Mostrando 0 a 0 de 0 entradas',
            infoFiltered: '(filtrado de _MAX_ entradas totales)',
            zeroRecords: 'No se encontraron resultados',
            emptyTable: 'Sin colaboradores todavía',
            paginate: { first: 'Primero', last: 'Último', next: 'Siguiente', previous: 'Anterior' },
        },
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [0, 5] }],
    });
</script>
@endpush
@endsection
