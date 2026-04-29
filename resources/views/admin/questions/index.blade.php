@extends('admin.layouts.app')

@section('title', 'Preguntas')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Preguntas del Quiz</h2>
        <p class="text-gray-500 text-sm mt-1">Gestiona todas las preguntas</p>
    </div>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('admin.questions.import') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Importar JSON
        </a>
        <a href="{{ route('admin.questions.create') }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
            + Nueva pregunta
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-4 md:p-6 overflow-x-auto">
        <table id="questions-table" class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 font-medium">
                    <th class="pb-3 pr-4">#</th>
                    <th class="pb-3 pr-4">Imagen</th>
                    <th class="pb-3 pr-4">Pregunta</th>
                    <th class="pb-3 pr-4">Respuesta</th>
                    <th class="pb-3 pr-4">Patrocinador</th>
                    <th class="pb-3">Acciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const deleteUrl = '{{ route('admin.questions.index') }}';

$('#questions-table').DataTable({
    ajax: {
        url: '{{ route('admin.questions.data') }}',
        type: 'GET',
    },
    serverSide: false,
    processing: true,
    language: {
        url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json',
    },
    columns: [
        { data: 'id', width: '50px' },
        {
            data: 'imagen',
            orderable: false,
            searchable: false,
            width: '64px',
            render: (data) => data
                ? `<img src="/storage/${data}" alt="img" class="h-10 w-10 rounded object-cover border border-gray-200">`
                : `<span class="text-gray-300 text-xs">—</span>`,
        },
        {
            data: 'pregunta',
            render: (data) => `<span class="line-clamp-2 max-w-xs block">${data}</span>`,
        },
        {
            data: 'respuesta_correcta',
            width: '100px',
            render: (data) => {
                const colors = { a: 'bg-blue-100 text-blue-700', b: 'bg-green-100 text-green-700', c: 'bg-yellow-100 text-yellow-700', d: 'bg-red-100 text-red-700' };
                return `<span class="px-2 py-0.5 rounded text-xs font-semibold uppercase ${colors[data] || ''}">${data}</span>`;
            },
        },
        {
            data: 'es_patrocinador',
            width: '100px',
            render: (data) => data
                ? '<span class="px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">⭐ Sí</span>'
                : '<span class="text-gray-400 text-xs">No</span>',
        },
        {
            data: 'id',
            orderable: false,
            searchable: false,
            width: '120px',
            render: (data) => `
                <div class="flex gap-2">
                    <a href="/admin/preguntas/${data}/editar"
                       class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded transition-colors">
                        Editar
                    </a>
                    <button onclick="deleteQuestion(${data})"
                            class="px-3 py-1 text-xs font-medium bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors">
                        Eliminar
                    </button>
                </div>
            `,
        },
    ],
    pageLength: 25,
    order: [[0, 'desc']],
});

function deleteQuestion(id) {
    if (!confirm('¿Eliminar esta pregunta?')) return;

    fetch(`/admin/preguntas/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#questions-table').DataTable().ajax.reload();
        }
    });
}
</script>
@endpush
