@extends('layouts.pegawai')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="container mx-auto px-4 md:px-8 lg:px-12 pb-12">
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-6xl mx-auto mt-6 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Riwayat Presensi</h3>
                <p class="text-sm text-gray-500">{{ $pegawai->nama_pegawai ?? 'Pegawai' }} · {{ $pegawai->nip ?? '-' }}</p>
            </div>
            <a href="{{ route('pegawai.presensi.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow hover:bg-blue-700 transition">
                <i class="fas fa-camera"></i>
                Presensi Hari Ini
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($presensis as $presensi)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->tanggal_presensi?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold {{ $presensi->type === 'masuk' ? ' text-blue-800' : ($presensi->type === 'pulang' ? ' text-orange-800' : 'bg-gray-100 text-gray-800') }}">
                                    <i class="fas {{ $presensi->type === 'masuk' ? 'fa-sign-in-alt' : ($presensi->type === 'pulang' ? 'fa-sign-out-alt' : 'fa-question-circle') }}"></i>
                                    {{ $presensi->type ? ucfirst($presensi->type) : '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                @if($presensi->status)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold 
                                        {{ in_array($presensi->status, ['Tepat Waktu']) ?  ' text-green-800' : '' }}
                                        {{ in_array($presensi->status, ['Terlambat']) ? '  text-yellow-800' : '' }}
                                        {{ in_array($presensi->status, ['Tidak Hadir']) ?  ' text-red-800' : '' }}
                                        {{ in_array($presensi->status, ['Pulang', 'Pulang Lebih Awal']) ? '  text-blue-800' : '' }}
                                    ">
                                        <i class="fas {{ in_array($presensi->status, ['Tepat Waktu']) ? 'fa-check-circle' : '' }} {{ in_array($presensi->status, ['Terlambat']) ? 'fa-hourglass-end' : '' }} {{ in_array($presensi->status, ['Tidak Hadir']) ? 'fa-times-circle' : '' }} {{ in_array($presensi->status, ['Pulang', 'Pulang Lebih Awal']) ? 'fa-sign-out-alt' : '' }}"></i>
                                        {{ $presensi->status }}
                                    </span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->jam_masuk ?? '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $presensi->jam_pulang ?? '-' }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col text-xs text-gray-700">
                                    <span>Lat: {{ $presensi->latitude ?? '-' }}</span>
                                    <span>Lng: {{ $presensi->longitude ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                @if($presensi->foto_presensi)
                                    <img src="{{ asset('storage/presensi/' . $presensi->foto_presensi) }}" alt="Foto Presensi" class="w-16 h-16 object-cover rounded-lg border">
                                @else
                                    <span class="text-gray-500 text-xs">Tidak ada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 block opacity-50"></i>
                                    <p class="text-sm font-medium">Belum ada riwayat presensi</p>
                                    <p class="text-xs mt-1">Presensi akan muncul di sini setelah Anda melakukan presensi</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-center">
            {{ $presensis->links() }}
        </div>
    </div>
</div>
@endsection
