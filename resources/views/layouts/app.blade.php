<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-poppins bg-gradient-to-br from-blue-100 via-white to-blue-50 min-h-screen">
    <div class="flex min-h-screen">
        @if(auth()->check() && auth()->user()->role === 'admin')
            @include('layouts.admin_sidebar')
        @endif

        <!-- Main Content -->
        <div class="flex-1 p-10 overflow-auto backdrop-blur-sm">
            <!-- Header -->
            <div class="flex justify-between items-center mb-10 border-b pb-4 border-gray-200">
                <h2 class="text-3xl font-extrabold text-gray-800">@yield('title', 'Dashboard Presensi')</h2>
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-user text-4xl text-blue-700"></i>
                    {{-- <span class="font-semibold text-gray-700">{{ Auth::user()->name }}</span> --}}
                </div>
            </div>

            <!-- Dynamic Content -->
            <div id="content-area" class="rounded-2xl">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
