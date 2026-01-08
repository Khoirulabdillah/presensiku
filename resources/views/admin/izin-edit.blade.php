@extends('layouts.app')

@section('title', 'Edit Permohonan Izin')

@section('content')

<div class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">

    <div class="max-w-3xl mx-auto">

        <div class="bg-white rounded-xl shadow overflow-hidden">

            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-white">Edit Permohonan Izin</h1>
                    <a href="{{ route('admin.izin.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg">Kembali</a>
                </div>
            </div>

            <div class="p-6">

                <form action="{{ route('admin.izin.update', $izin->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Nama Pegawai</label>
                        <div class="mt-1 text-gray-900">{{ $izin->pegawai->nama_pegawai ?? 'N/A' }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Jenis Izin</label>
                        <div class="mt-1 text-gray-900">{{ $izin->jenis_izin }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status_izin" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="pending" {{ $izin->status_izin == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $izin->status_izin == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $izin->status_izin == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Catatan Admin (opsional)</label>
                        <textarea name="catatan_admin" rows="4" class="mt-1 block w-full rounded-md border-gray-300">{{ $izin->catatan_admin ?? '' }}</textarea>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('admin.izin.index') }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan Perubahan</button>
                    </div>

                </form>

            </div>

        </div>
    </div>

</div>

@endsection
