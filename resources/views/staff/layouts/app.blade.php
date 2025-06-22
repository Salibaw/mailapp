<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Dashboard staff</title>
    @vite(['resources/css/app.css'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-indigo-800 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-2xl font-bold">Dashboard Staff</h1>
                <p class="text-sm mt-2">Selamat datang, {{ auth()->user()->name }}</p>
            </div>
            <nav class="mt-4">
                <ul>
                    <li>
                        <a href="{{ route('staff.dashboard') }}"
                            class="flex items-center px-4 py-2 {{ request()->routeIs('staff.dashboard') ? 'bg-indigo-600' : 'hover:bg-indigo-700' }}">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.surat-masuk.index') }}"
                            class="flex items-center px-4 py-2 {{ request()->routeIs('staff.surat-masuk.*') ? 'bg-indigo-600' : 'hover:bg-indigo-700' }}">
                            <i class="fas fa-inbox mr-2"></i> Surat Masuk
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.surat-keluar.index') }}"
                            class="flex items-center px-4 py-2 {{ request()->routeIs('staff.surat-keluar.*') ? 'bg-indigo-600' : 'hover:bg-indigo-700' }}">
                            <i class="fas fa-paper-plane mr-2"></i> Surat Keluar
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.disposisi.index') }}"
                            class="flex items-center px-4 py-2 {{ request()->routeIs('staff.disposisi.*') ? 'bg-indigo-600' : 'hover:bg-indigo-700' }}">
                            <i class="fas fa-random mr-2"></i> Disposisi
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.templates.index') }}"
                            class="flex items-center px-4 py-2 {{ request()->routeIs('staff.template.*') ? 'bg-indigo-600' : 'hover:bg-indigo-700' }}">
                            <i class="fas fa-file-alt mr-2"></i> Template
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="flex items-center px-4 py-2 hover:bg-indigo-700">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Content Area -->
        <div class="flex-1 ml-64 p-6">
            <header class="bg-white shadow p-4 mb-6 rounded-lg">
                <h2 class="text-xl font-semibold text-gray-800">@yield('page-title')</h2>
            </header>
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    @stack('scripts')

</body>

</html>