@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai')

@section('content')
<div class="container mx-auto px-4 md:px-8 lg:px-12 pb-10">
    {{-- Kartu Info Kehadiran --}}
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-4xl mx-auto -mt-14 p-4 sm:p-6 lg:p-8 transform transition duration-300 hover:shadow-2xl">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            {{-- Informasi Jadwal --}}
            <div>
                {{-- <p class="text-xl font-bold text-gray-800">Reguler</p> --}}
                <p class="text-sm text-gray-500 mt-1" id="tanggal">Senin, 11 November 2025</p>
                <p class="text-sm text-gray-600 mt-1">08.00 - 17.00 WIB</p>
                <p class="text-md font-semibold text-green-600 mt-2">Masuk: 08:00</p>
            </div>
            {{-- Informasi Pulang --}}
            <div class="text-left sm:text-right mt-2 sm:mt-0">
                <p class="text-md font-semibold text-red-600">Pulang: 17:00</p>
            </div>
        </div>

        {{-- Statistik Kehadiran (Lebih Responsif) --}}
        <div class="flex justify-around text-center mt-6 pt-4 border-t border-gray-100">
            <div class="w-1/3 p-1">
                <p class="text-xl sm:text-2xl font-bold text-blue-600">5</p>
                <p class="text-xs sm:text-sm text-gray-600">Hadir</p>
                <div class="h-1 w-full bg-blue-600 mx-auto rounded-full mt-2"></div>
            </div>
            
            <div  class="w-1/3 p-1 border-l border-r border-gray-100 block">
                <p class="text-xl sm:text-2xl font-bold text-yellow-500">0</p>
                <p class="text-xs sm:text-sm text-gray-600">Izin</p>
                <div class="h-1 w-full bg-yellow-500 mx-auto rounded-full mt-2"></div>
            </div>
            <div class="w-1/3 p-1">
                <p class="text-xl sm:text-2xl font-bold text-green-500">0</p>
                <p class="text-xs sm:text-sm text-gray-600">Cuti</p>
                <div class="h-1 w-full bg-green-500 mx-auto rounded-full mt-2"></div>
            </div>
        </div>
    </div>
    {{-- Akhir Kartu Info Kehadiran --}}

    {{-- Menu Utama --}}
    <div class="mt-12 px-2 sm:px-4 lg:px-6 pb-24">
        <h3 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-2">Menu Utama</h3>
        
        {{-- Tata Letak Grid yang Dioptimalkan --}}
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-7 gap-6 sm:gap-8">
            
            {{-- Menu Presensi --}}
            <div class="flex flex-col items-center">
                <a href="{{ route('pegawai.presensi.index') }}" class="bg-white shadow-lg rounded-2xl w-full max-w-[90px] h-20 sm:h-24 flex items-center justify-center transition hover:shadow-xl hover:scale-105 duration-200 aspect-square">
                    <i class="fa-solid fa-camera text-3xl sm:text-4xl text-blue-600"></i>
                </a>
                <span class="text-sm sm:text-md font-medium text-gray-700 mt-2 text-center">Presensi</span>
            </div>

            {{-- Menu Cuti --}}
            <div class="flex flex-col items-center">
                <a href="{{ route('pegawai.izin.create') }}" class="bg-white shadow-lg rounded-2xl w-full max-w-[90px] h-20 sm:h-24 flex items-center justify-center transition hover:shadow-xl hover:scale-105 duration-200 aspect-square">
                    <i class="fa-solid fa-file-lines text-3xl sm:text-4xl text-yellow-600"></i>
                </a>
                <span class="text-sm sm:text-md font-medium text-gray-700 mt-2 text-center">Izin</span>
            </div>

            {{-- Menu Riwayat (Contoh Penambahan) --}}
            <div class="flex flex-col items-center">
                <a  class="bg-white shadow-lg rounded-2xl w-full max-w-[90px] h-20 sm:h-24 flex items-center justify-center transition hover:shadow-xl hover:scale-105 duration-200 aspect-square">
                    <i class="fa-solid fa-clock-rotate-left text-3xl sm:text-4xl text-green-600"></i>
                </a>
                <span class="text-sm sm:text-md font-medium text-gray-700 mt-2 text-center">Riwayat Presensi</span>
            </div>
        </div>
    </div>
            <!-- Logout -->
        <form action="{{ route('logout') }}" method="POST" class="mt-auto">
            @csrf
            <button type="submit" class="flex items-center justify-center gap-3 w-full px-4 py-2 mt-4 bg-blue-800/80 hover:bg-red-600 transition rounded-xl">
                <i class="fas fa-sign-out-alt text-lg"></i>
                <span>Logout</span>
            </button>
        </form>
    {{-- Akhir Menu Utama --}}
</div>
@endsection