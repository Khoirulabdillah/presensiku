<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'Dashboard Pegawai')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="font-poppins bg-gradient-to-br from-green-100 via-white to-green-50 min-h-screen">
    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-green-900 to-green-700 text-white p-5 flex flex-col relative shadow-2xl rounded-r-3xl">
            <!-- Logo -->
            <div class="flex items-center justify-center space-x-2 mb-10">
                <img src="/images/logo-presensi.png" alt="Presensi Logo" class="h-12 w-auto">
                <span class="text-xl font-bold">Presensi</span>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-2">
                <a href="{{ route('pegawai.home') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl bg-green-800/80 hover:bg-green-600 transition">
                    <i class="fas fa-home text-lg"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('pegawai.izin.create') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-green-600 transition">
                    <i class="fas fa-plus-circle text-lg"></i>
                    <span>Ajukan Izin</span>
                </a>
                <a href="{{ route('pegawai.izin.history') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-green-600 transition">
                    <i class="fas fa-history text-lg"></i>
                    <span>Riwayat Izin</span>
                </a>
                <a class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-green-600 transition">
                    <i class="fas fa-calendar-check text-lg"></i>
                    <span>Presensi</span>
                </a>
            </nav>

            <!-- Logout -->
            <form action="{{ route('logout') }}" method="POST" class="mt-auto">
                @csrf
                <button type="submit" class="flex items-center justify-center gap-3 w-full px-4 py-2 mt-4 bg-green-800/80 hover:bg-red-600 transition rounded-xl">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-10 overflow-auto backdrop-blur-sm">
            <!-- Header -->
            <div class="flex justify-between items-center mb-10 border-b pb-4 border-gray-200">
                <h2 class="text-3xl font-extrabold text-gray-800">Dashboard Pegawai</h2>
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-user text-4xl text-green-700"></i>
                    <span class="font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>

            <!-- Dynamic Content -->
            <div id="content-area" class="rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>