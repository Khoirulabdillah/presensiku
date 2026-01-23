<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard Pegawai')</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="bg-white/10 rounded-full p-1 sm:p-2">
            <div class="bg-white rounded-full w-14 h-14 sm:w-16 sm:h-16 flex items-center justify-center text-blue-600 text-2xl sm:text-3xl">
              <i class="fa-solid fa-user"></i>
            </div>
          </div>
          <div>
            <h2 class="font-semibold text-base sm:text-lg">{{ Auth::user()->name }}</h2>
            <div class="text-xs sm:text-sm text-white/90">Pegawai • <span id="tanggal" class="font-medium"></span></div>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <form action="{{ route('logout') }}" method="POST" class="sm:inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 px-3 py-2 rounded-lg transition">            
            @csrf
            <i class="fas fa-sign-out-alt text-base"></i>
            <button type="submit" class="text-sm">Logout</button>
          </form>
        </div>
      </div>
    </div>

    <div class="pointer-events-none">
      <svg viewBox="0 0 1200 80" preserveAspectRatio="none" class="w-full h-8 block text-white/10">
        <path d="M0,0 C200,80 400,0 600,40 C800,80 1000,0 1200,40 L1200,80 L0,80 Z" fill="currentColor" />
      </svg>
    </div>
  </header>

  <!-- Dynamic Content -->
  <main class="flex-1">
    @yield('content')
  </main>
  <script>
    // Menampilkan tanggal hari ini (format: Sen, 23/01)
    document.addEventListener("DOMContentLoaded", () => {
      const options = { weekday: 'short', day: '2-digit', month: '2-digit' };
      const today = new Date().toLocaleDateString('id-ID', options);
      const tanggalEl = document.getElementById('tanggal');
      if (tanggalEl) tanggalEl.textContent = today;
    });
  </script>
</body>
</html>
