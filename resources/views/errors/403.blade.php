@extends('layouts.app')

@section('title', '403 - Unauthorized')

@section('content')
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white shadow-xl rounded-2xl p-8 text-center max-w-lg">
            <h1 class="text-4xl font-extrabold text-red-600 mb-4">403</h1>
            <p class="text-lg text-gray-700 mb-4">Unauthorized — Anda tidak memiliki akses ke halaman ini.</p>
            <p class="text-sm text-gray-500 mb-6">Jika Anda merasa ini sebuah kesalahan, hubungi administrator.</p>
            <a href="{{ url('/') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg">Kembali ke Beranda</a>
        </div>
    </div>
@endsection
