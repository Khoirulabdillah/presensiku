@extends('layouts.app')

@section('title', 'Kelola Izin')

@section('content')

<div class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">

    <div class="max-w-7xl mx-auto">

        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-check text-white text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-white">Kelola Permohonan Izin</h1>
                    </div>
                    <a href="{{ route('admin.pegawai.index') }}"
                       class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Pegawai
                    </a>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-6 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-6 mt-4">
                    <strong class="font-bold">Oops!</strong>
                    <span class="block sm:inline">Ada kesalahan dalam proses.</span>
                </div>
            @endif

            <!-- Content -->
            <div class="p-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 rounded-full p-3">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-600">Pending</p>
                                <p class="text-2xl font-bold text-yellow-800">{{ $izin->where('status_izin', 'pending')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-green-100 rounded-full p-3">
                                <i class="fas fa-check text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-600">Approved</p>
                                <p class="text-2xl font-bold text-green-800">{{ $izin->where('status_izin', 'approved')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-red-100 rounded-full p-3">
                                <i class="fas fa-times text-red-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-red-600">Rejected</p>
                                <p class="text-2xl font-bold text-red-800">{{ $izin->where('status_izin', 'rejected')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="bg-blue-100 rounded-full p-3">
                                <i class="fas fa-list text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-600">Total</p>
                                <p class="text-2xl font-bold text-blue-800">{{ $izin->total() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pegawai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Izin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($izin as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->pegawai->nama_pegawai ?? 'N/A' }}</div>
                                                <div class="text-sm text-gray-500">NIP: {{ $item->nip }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $item->jenis_izin }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}</div>
                                        @if($item->tanggal_selesai)
                                            <div class="text-sm text-gray-500">s/d {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->status_izin == 'pending')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>Pending
                                            </span>
                                        @elseif($item->status_izin == 'approved')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                <i class="fas fa-check mr-1"></i>Approved
                                            </span>
                                        @elseif($item->status_izin == 'rejected')
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                <i class="fas fa-times mr-1"></i>Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.izin.show', $item->id) }}"
                                               class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md">
                                                <i class="fas fa-eye mr-1"></i>Lihat
                                            </a>
                                            <a href="{{ route('admin.izin.edit', $item->id) }}"
                                               class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 px-3 py-1 rounded-md">
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </a>
                                            <form action="{{ route('admin.izin.destroy', $item->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan izin ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md">
                                                    <i class="fas fa-trash mr-1"></i>Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>Belum ada permohonan izin</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($izin->hasPages())
                    <div class="mt-6">
                        {{ $izin->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

@endsection