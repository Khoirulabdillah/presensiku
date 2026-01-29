<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="lg:hidden fixed inset-0 bg-black/50 z-[9998] hidden transition-opacity"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 z-[9999] w-64 h-screen bg-gradient-to-b from-blue-900 to-blue-700 text-white p-5 flex flex-col shadow-2xl lg:shadow-none rounded-r-3xl lg:rounded-none transition-transform duration-300 -translate-x-full lg:translate-x-0">
    <!-- Close Button (Mobile) -->
    <button id="sidebar-close" class="lg:hidden absolute top-5 right-5 text-white hover:text-gray-200 transition">
        <i class="fas fa-times text-xl"></i>
    </button>

    <!-- Logo -->
    <div class="flex items-center justify-center space-x-2 mb-10 mt-8 lg:mt-0">
        <img src="/images/logo-presensi.png" alt="Presensi Logo" class="h-12 w-auto">
        <span class="text-xl font-bold hidden sm:inline">Presensi</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
            class="flex items-center gap-3 py-2 px-4 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-blue-500 text-white font-semibold' : 'bg-blue-800/50 hover:bg-blue-600 text-blue-100' }}">
            <i class="fas fa-tachometer-alt text-lg"></i>
            <span>Dashboard</span>
        </a>

        <!-- Presensi -->
        <a href="{{ route('admin.presensi.index') }}" 
            class="flex items-center gap-3 py-2 px-4 rounded-xl transition {{ request()->routeIs('admin.presensi.*') ? 'bg-blue-500 text-white font-semibold' : 'bg-blue-800/50 hover:bg-blue-600 text-blue-100' }}">
            <i class="fas fa-calendar-check text-lg"></i>
            <span>Presensi</span>
        </a>

        <!-- Izin -->
        <a href="{{ route('admin.izin.index') }}" 
            class="flex items-center gap-3 py-2 px-4 rounded-xl transition {{ request()->routeIs('admin.izin.*') ? 'bg-blue-500 text-white font-semibold' : 'bg-blue-800/50 hover:bg-blue-600 text-blue-100' }}">
            <i class="fas fa-file-alt text-lg"></i>
            <span>Izin</span>
        </a>

        <!-- Pegawai -->
        <a href="{{ route('admin.pegawai.index') }}"
            class="flex items-center gap-3 py-2 px-4 rounded-xl transition {{ request()->routeIs('admin.pegawai.*') ? 'bg-blue-500 text-white font-semibold' : 'bg-blue-800/50 hover:bg-blue-600 text-blue-100' }}">
            <i class="fas fa-users text-lg"></i>
            <span>Pegawai</span>
        </a>

        <!-- Lokasi Kantor -->
        <a href="{{ route('admin.office-settings.index') }}" 
            class="flex items-center gap-3 py-2 px-4 rounded-xl transition {{ request()->routeIs('admin.office-settings.*') ? 'bg-blue-500 text-white font-semibold' : 'bg-blue-800/50 hover:bg-blue-600 text-blue-100' }}">
            <i class="fas fa-map-marker-alt text-lg"></i>
            <span>Lokasi Kantor</span>
        </a>
    </nav>

    <!-- Logout -->
    <form action="{{ route('logout') }}" method="POST" class="mt-auto">
        @csrf
        <button type="submit" class="flex items-center justify-center gap-3 w-full px-4 py-2 mt-4 bg-red-600 hover:bg-red-700 transition rounded-xl font-semibold">
            <i class="fas fa-sign-out-alt text-lg"></i>
            <span>Logout</span>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const close = document.getElementById('sidebar-close');
    const overlay = document.getElementById('sidebar-overlay');

    // Toggle sidebar
    toggle.addEventListener('click', function() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    // Close sidebar
    close.addEventListener('click', function() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // Close sidebar when clicking menu item (mobile)
    const menuLinks = sidebar.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('hidden');
        }
    });
});
</script>