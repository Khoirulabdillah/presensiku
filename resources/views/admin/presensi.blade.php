@extends('layouts.app')

@section('title', 'Kelola Presensi')

@section('content')

<div class="bg-white shadow-xl rounded-2xl w-full max-w-6xl mx-auto p-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Data Presensi</h3>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latitude</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Longitude</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($presensis as $presensi)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->nip }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->pegawai->nama ?? 'N/A' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->tanggal_presensi->format('d/m/Y') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($presensi->type) }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->jam_masuk }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->jam_pulang }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->latitude }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->longitude }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-2 text-center text-sm text-gray-500">Belum ada data presensi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $presensis->links() }}
    </div>
</div>

@endsection