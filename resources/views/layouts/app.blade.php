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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-poppins bg-gradient-to-br from-blue-100 via-white to-blue-50 min-h-screen">
    <div class="flex min-h-screen flex-col lg:flex-row">
        @if(auth()->check() && auth()->user()->role === 'admin')
            @include('layouts.admin_sidebar')
        @endif

        <!-- Main Content -->
        <div class="flex-1 flex flex-col w-full lg:overflow-auto lg:ml-64">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white py-6 lg:py-8 px-4 lg:px-8 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h1 class="text-2xl lg:text-4xl font-bold">@yield('title', 'Dashboard')</h1>
                    </div>
                    @if(auth()->check() && auth()->user()->role === 'admin')
                        <button id="sidebar-toggle" class="lg:hidden z-50 p-2 hover:bg-blue-800 rounded-lg transition ml-4">
                            <i class="fas fa-bars text-2xl"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Dynamic Content -->
            <div class="flex-1 p-4 lg:p-10 overflow-auto">
                <div id="content-area" class="rounded-2xl">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
