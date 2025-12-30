@extends('layouts.app')

@section('title', 'Setting Waktu Kerja')

@section('content')

<div class="bg-white shadow-xl rounded-2xl w-full max-w-6xl mx-auto p-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Setting Waktu Kerja</h3>

    <form action="{{ route('admin.office-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="jam_masuk" class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                <input type="time" id="jam_masuk" name="jam_masuk" value="{{ $officeSetting->jam_masuk ?? '08:00' }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>

            <div>
                <label for="jam_pulang" class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                <input type="time" id="jam_pulang" name="jam_pulang" value="{{ $officeSetting->jam_pulang ?? '17:00' }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                <i class="fas fa-save mr-2"></i>Simpan Setting Waktu
            </button>
        </div>
    </form>
</div>

@endsection