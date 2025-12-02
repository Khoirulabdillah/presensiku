<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Dashboard Pegawai')</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>

<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

  <!-- Header -->
  <div class="bg-blue-600 rounded-b-[50px] text-white p-6 pb-20 text-center relative">
    <div class="flex flex-col items-center">
      <div class="bg-white rounded-full w-16 h-16 flex items-center justify-center text-blue-600 text-3xl mb-3">
        <i class="fa-solid fa-user"></i>
      </div>
      <div>
        <h2 class="font-semibold text-lg">{{ Auth::user()->name }}</h2>
        <p class="text-sm opacity-90">{{ Auth::user()->employee_id ?? '3003872632' }}</p>
      </div>
    </div>
  </div>

  <!-- Dynamic Content -->
  <main class="flex-1">
    @yield('content')
  </main>
    </div>
    </div>


  <script>
    // Menampilkan tanggal hari ini
    document.addEventListener("DOMContentLoaded", () => {
      const options = { weekday: 'long', day: '2-digit', month: '2-digit' };
      const today = new Date().toLocaleDateString('id-ID', options);
      const tanggalEl = document.getElementById('tanggal');
      if (tanggalEl) tanggalEl.textContent = today;
    });
  </script>
</body>
</html>
