@extends('admin.layouts.app')

@section('title', 'Participantes')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Participantes</h2>
        <p class="text-gray-500 text-sm mt-1">Listado de participantes del quiz</p>
    </div>
    <div class="flex gap-3">
        <button id="btn-bulk-delete"
                class="hidden px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
            Eliminar seleccionados (<span id="selected-count">0</span>)
        </button>
        <button onclick="deleteAll()"
                class="px-4 py-2 border border-red-300 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">
            Borrar todos
        </button>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6">
        <table id="participants-table" class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 font-medium">
                    <th class="pb-3 pr-4">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300">
                    </th>
                    <th class="pb-3 pr-4">#</th>
                    <th class="pb-3 pr-4">Jugador</th>
                    <th class="pb-3 pr-4">Puntos</th>
                    <th class="pb-3 pr-4">Preguntas</th>
                    <th class="pb-3 pr-4">%</th>
                    <th class="pb-3 pr-4">Tiempo</th>
                    <th class="pb-3 pr-4">Fecha</th>
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
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const table = $('#participants-table').DataTable({
    ajax: {
        url: '{{ route('admin.participants.data') }}',
        type: 'GET',
    },
    serverSide: false,
    processing: true,
    language: {
        url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json',
    },
    columns: [
        {
            data: null,
            orderable: false,
            searchable: false,
            width: '40px',
            render: (data, type, row) =>
                `<input type="checkbox" class="row-checkbox rounded border-gray-300" value="${row.id}">`,
        },
        { data: 'id', width: '50px' },
        { data: 'nombre_jugador' },
        {
            data: 'puntos',
            width: '80px',
            render: (data) => `<span class="font-semibold text-blue-700">${data}</span>`,
        },
        { data: 'total_preguntas', width: '90px' },
        {
            data: 'porcentaje',
            width: '70px',
            render: (data) => `${parseFloat(data).toFixed(1)}%`,
        },
        {
            data: 'tiempo',
            width: '80px',
            render: (data) => data != null ? formatTime(data) : '<span class="text-gray-300">—</span>',
        },
        {
            data: 'created_at',
            width: '130px',
            render: (data) => new Date(data).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }),
        },
        {
            data: 'id',
            orderable: false,
            searchable: false,
            width: '120px',
            render: (data) => `
                <div class="flex gap-2">
                    <a href="/admin/participantes/${data}/editar"
                       class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded transition-colors">
                        Editar
                    </a>
                    <button onclick="deleteOne(${data})"
                            class="px-3 py-1 text-xs font-medium bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors">
                        Eliminar
                    </button>
                </div>
            `,
        },
    ],
    pageLength: 25,
    order: [[1, 'desc']],
    drawCallback: updateSelectionState,
});

function formatTime(seconds) {
    if (seconds < 60) return `${seconds}s`;
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}m ${s}s`;
}

// Select all
$('#select-all').on('change', function () {
    const checked = this.checked;
    $('#participants-table tbody .row-checkbox').prop('checked', checked);
    updateBulkButton();
});

$(document).on('change', '.row-checkbox', updateBulkButton);

function updateBulkButton() {
    const count = $('.row-checkbox:checked').length;
    $('#selected-count').text(count);
    if (count > 0) {
        $('#btn-bulk-delete').removeClass('hidden');
    } else {
        $('#btn-bulk-delete').addClass('hidden');
    }
    $('#select-all').prop('indeterminate', count > 0 && count < $('.row-checkbox').length);
    $('#select-all').prop('checked', count > 0 && count === $('.row-checkbox').length);
}

function updateSelectionState() {
    updateBulkButton();
}

$('#btn-bulk-delete').on('click', function () {
    const ids = $('.row-checkbox:checked').map((_, el) => parseInt(el.value)).get();
    if (!ids.length) return;
    if (!confirm(`¿Eliminar los ${ids.length} participantes seleccionados?`)) return;

    fetch('{{ route('admin.participants.bulk-destroy') }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            table.ajax.reload();
            $('#btn-bulk-delete').addClass('hidden');
        }
    });
});

function deleteOne(id) {
    if (!confirm('¿Eliminar este participante?')) return;

    fetch(`/admin/participantes/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) table.ajax.reload();
    });
}

function deleteAll() {
    const total = table.data().count();
    if (!total) return alert('No hay participantes que eliminar.');
    if (!confirm(`¿Eliminar TODOS los ${total} participantes? Esta acción no se puede deshacer.`)) return;

    fetch('{{ route('admin.participants.destroy-all') }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) table.ajax.reload();
    });
}
</script>
@endpush
