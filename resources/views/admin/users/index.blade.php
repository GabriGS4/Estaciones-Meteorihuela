@extends('admin.layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Usuarios</h2>
        <p class="text-gray-500 text-sm mt-1">Administradores del panel</p>
    </div>
    <a href="{{ route('admin.users.create') }}"
       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
        + Nuevo usuario
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6">
        <table id="users-table" class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 font-medium">
                    <th class="pb-3 pr-4">#</th>
                    <th class="pb-3 pr-4">Nombre</th>
                    <th class="pb-3 pr-4">Email</th>
                    <th class="pb-3 pr-4">Fecha de creación</th>
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
const currentUserId = {{ auth()->id() }};

const table = $('#users-table').DataTable({
    ajax: {
        url: '{{ route('admin.users.data') }}',
        type: 'GET',
    },
    serverSide: false,
    processing: true,
    language: {
        url: 'https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json',
    },
    columns: [
        { data: 'id', width: '60px' },
        {
            data: 'name',
            render: (data, type, row) => {
                const badge = row.id === currentUserId
                    ? ' <span class="ml-1 text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Tú</span>'
                    : '';
                return `<span class="font-medium text-gray-900">${data}</span>${badge}`;
            },
        },
        { data: 'email' },
        {
            data: 'created_at',
            width: '150px',
            render: (data) => new Date(data).toLocaleDateString('es-ES', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit',
            }),
        },
        {
            data: 'id',
            orderable: false,
            searchable: false,
            width: '130px',
            render: (data, type, row) => {
                const deleteBtn = row.id !== currentUserId
                    ? `<button onclick="deleteOne(${data})"
                               class="px-3 py-1 text-xs font-medium bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors">
                           Eliminar
                       </button>`
                    : '';
                return `
                    <div class="flex gap-2">
                        <a href="/admin/usuarios/${data}/editar"
                           class="px-3 py-1 text-xs font-medium bg-gray-100 hover:bg-gray-200 rounded transition-colors">
                            Editar
                        </a>
                        ${deleteBtn}
                    </div>
                `;
            },
        },
    ],
    pageLength: 25,
    order: [[0, 'asc']],
});

function deleteOne(id) {
    if (!confirm('¿Eliminar este usuario?')) return;

    fetch(`/admin/usuarios/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            table.ajax.reload();
        } else {
            alert(data.message ?? 'Error al eliminar el usuario.');
        }
    });
}
</script>
@endpush
