@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header Greeting --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-lg sm:text-2xl font-semibold text-gray-800">Halo, selamat datang</h1>
            <p class="text-sm text-gray-500">Dashboard pegawai — ringkasan kehadiran Anda</p>
        </div>
    </div>

    {{-- Kartu Info Kehadiran --}}
    <div class="bg-white shadow-md rounded-2xl p-4 sm:p-6 mb-6">
        <div class="grid grid-cols-3 sm:grid-cols-3 gap-4 text-center items-center">
            <div class="p-3 rounded-lg bg-gradient-to-br from-blue-50 to-white">
                <p class="text-2xl sm:text-3xl font-bold text-blue-600">{{ $hadir }}</p>
                <p class="text-xs sm:text-sm text-gray-500">Hadir</p>
            </div>
            <div class="p-3 rounded-lg bg-gradient-to-br from-yellow-50 to-white">
                <p class="text-2xl sm:text-3xl font-bold text-yellow-500">{{ $izin }}</p>
                <p class="text-xs sm:text-sm text-gray-500">Izin</p>
            </div>
            <div class="p-3 rounded-lg bg-gradient-to-br from-red-50 to-white">
                <p class="text-2xl sm:text-3xl font-bold text-red-500">{{ $cuti }}</p>
                <p class="text-xs sm:text-sm text-gray-500">Tidak Hadir</p>
            </div>
        </div>
    </div>

    {{-- Menu Utama --}}
    <section class="mb-10">
        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Menu Utama</h3>

        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
            {{-- Presensi --}}
            <a href="{{ route('pegawai.presensi.index') }}" class="group flex flex-col items-center bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transform transition hover:-translate-y-1">
                <div class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center rounded-xl bg-blue-50 group-hover:bg-blue-100 transition">
                    <i class="fa-solid fa-camera text-2xl sm:text-3xl text-blue-600"></i>
                </div>
                <span class="mt-2 text-xs sm:text-sm font-medium text-gray-700 text-center">Presensi</span>
            </a>

            {{-- Izin --}}
            <a href="{{ route('pegawai.izin.create') }}" class="group flex flex-col items-center bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transform transition hover:-translate-y-1">
                <div class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center rounded-xl bg-yellow-50 group-hover:bg-yellow-100 transition">
                    <i class="fa-solid fa-file-lines text-2xl sm:text-3xl text-yellow-600"></i>
                </div>
                <span class="mt-2 text-xs sm:text-sm font-medium text-gray-700 text-center">Izin</span>
            </a>

            {{-- Riwayat Presensi --}}
            <a href="{{ route('pegawai.riwayat_presensi.index') }}" class="group flex flex-col items-center bg-white rounded-2xl p-3 shadow-sm hover:shadow-md transform transition hover:-translate-y-1">
                <div class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center rounded-xl bg-green-50 group-hover:bg-green-100 transition">
                    <i class="fa-solid fa-clock-rotate-left text-2xl sm:text-3xl text-green-600"></i>
                </div>
                <span class="mt-2 text-xs sm:text-sm font-medium text-gray-700 text-center">Riwayat</span>
            </a>

            {{-- Placeholder / extensible items (fills grid nicely on larger screens) --}}
            <div class="hidden md:flex items-center justify-center bg-transparent"></div>
            <div class="hidden lg:flex items-center justify-center bg-transparent"></div>
            <div class="hidden xl:flex items-center justify-center bg-transparent"></div>
        </div>
    </section>
</div>
@endsection