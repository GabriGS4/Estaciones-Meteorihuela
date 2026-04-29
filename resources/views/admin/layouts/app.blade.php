<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') — App MeteOrihuela</title>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Sidebar + Content -->
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
            <div class="px-6 py-5 border-b border-gray-700">
                <h1 class="text-lg font-bold tracking-tight">⛅ App MeteOrihuela</h1>
                <p class="text-xs text-gray-400 mt-1">
                    @if(auth()->user()->is_admin)
                        Panel de administración
                    @else
                        Panel de colaborador
                    @endif
                </p>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">

                @if(auth()->user()->is_admin)
                    {{-- Navegación de administrador --}}
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Colaboradores</p>
                        <a href="{{ route('admin.sponsors.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.sponsors.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Colaboradores
                        </a>
                    </div>

                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Quiz</p>
                        <a href="{{ route('admin.questions.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.questions.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Preguntas
                        </a>
                        <a href="{{ route('admin.participants.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium mt-1 {{ request()->routeIs('admin.participants.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Participantes
                        </a>
                    </div>

                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Sistema</p>
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Usuarios
                        </a>
                    </div>

                    <div class="pt-4">
                        <p class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Configuración</p>
                        <a href="{{ route('admin.settings.car-wash') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.car-wash*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Lavadero de coche
                        </a>
                        <a href="{{ route('admin.settings.beaches') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium mt-1 {{ request()->routeIs('admin.settings.beaches*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Playas
                        </a>
                        <a href="{{ route('admin.settings.quiz') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.quiz*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            Test
                        </a>
                    </div>

                @else
                    {{-- Navegación de colaborador --}}
                    <a href="{{ route('admin.mi-colaborador.show') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.mi-colaborador.show') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Mi colaborador
                    </a>
                    <a href="{{ route('admin.mi-colaborador.password') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium mt-1 {{ request()->routeIs('admin.mi-colaborador.password') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        Cambiar contraseña
                    </a>
                @endif
            </nav>

            <div class="px-4 py-4 border-t border-gray-700">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-xs text-gray-400 hover:text-white transition-colors px-3 py-1.5 rounded hover:bg-gray-800">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <main class="flex-1 overflow-auto">
            <div class="px-8 py-6">
                @if(session('success'))
                    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.min.js"></script>
    @stack('scripts')
</body>
</html>
