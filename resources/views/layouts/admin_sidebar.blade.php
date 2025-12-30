<!-- Sidebar -->
<div class="w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-5 flex flex-col relative shadow-2xl rounded-r-3xl">
    <!-- Logo -->
    <div class="flex items-center justify-center space-x-2 mb-10">
        <img src="/images/logo-presensi.png" alt="Presensi Logo" class="h-12 w-auto">
        <span class="text-xl font-bold">Presensi</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl bg-blue-800/80 hover:bg-blue-600 transition">
            <i class="fas fa-tachometer-alt text-lg"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.presensi.index') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-blue-600 transition">
            <i class="fas fa-calendar-check text-lg"></i>
            <span>Presensi</span>
        </a>
        <a href="{{ route('admin.izin.index') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-blue-600 transition">
            <i class="fas fa-file-alt text-lg"></i>
            <span>Izin</span>
        </a>
        <a
            href="{{ route('admin.pegawai.index') }}"
            class="flex items-center justify-between w-full gap-3 py-2 px-4 rounded-xl hover:bg-blue-600 transition bg-blue-500 text-white">
            <div class="flex items-center gap-3">
                <i class="fas fa-users text-lg"></i>
                <span>Pegawai</span>
            </div>
        </a>
        <a href="{{ route('admin.office-settings.index') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-blue-600 transition">
            <i class="fas fa-map-marker-alt text-lg"></i>
            <span>Lokasi Kantor</span>
        </a>
        <a href="{{ route('admin.setting-waktu.index') }}" class="flex items-center gap-3 py-2 px-4 rounded-xl hover:bg-blue-600 transition">
            <i class="fa-solid fa-clock text-lg"></i>
            <span>Seting Waktu</span>
        </a>
    </nav>

    <!-- Logout -->
    <form action="{{ route('logout') }}" method="POST" class="mt-auto">
        @csrf
        <button type="submit" class="flex items-center justify-center gap-3 w-full px-4 py-2 mt-4 bg-blue-800/80 hover:bg-red-600 transition rounded-xl">
            <i class="fas fa-sign-out-alt text-lg"></i>
            <span>Logout</span>
        </button>
    </form>
</div>