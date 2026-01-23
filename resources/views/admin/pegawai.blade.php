@extends('layouts.app')

@section('title', 'Kelola Pegawai')

@section('content')

<div class="bg-white shadow-xl rounded-2xl w-full max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Data Pegawai</h3>
        <a href="{{ route('admin.pegawai.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
            <i class="fas fa-plus mr-2"></i>Tambah Pegawai
        </a>
    </div>

    <!-- Filter Section -->
    <div class="mb-6">
        <form method="GET" action="{{ route('admin.pegawai.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="divisi" class="block text-sm font-medium text-gray-700 mb-2">Filter Divisi</label>
                    <select id="divisi" name="divisi" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Semua Divisi --</option>
                        @foreach($divisis as $divisi)
                            <option value="{{ $divisi->id }}" {{ request('divisi') == $divisi->id ? 'selected' : '' }}>
                                {{ $divisi->nama_divisi }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jabatan" class="block text-sm font-medium text-gray-700 mb-2">Filter Jabatan</label>
                    <select id="jabatan" name="jabatan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Semua Jabatan --</option>
                        @foreach($jabatans as $jabatan)
                            <option value="{{ $jabatan }}" {{ request('jabatan') == $jabatan ? 'selected' : '' }}>
                                {{ $jabatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                        <i class="fas fa-search"></i>
                        Cari
                    </button>
                    <a href="{{ route('admin.pegawai.index') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition font-medium">
                        <i class="fas fa-redo"></i>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Divisi</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if($pegawais->count() > 0)
                    @foreach($pegawais as $pegawai)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $pegawai->nip }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $pegawai->nama_pegawai }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold  text-purple-800">
                                <i class="fas fa-sitemap"></i>
                                {{ $pegawai->divisi->nama_divisi ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            @if($pegawai->jabatan)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold  text-indigo-800">
                                    <i class="fas fa-briefcase"></i>
                                    {{ $pegawai->jabatan }}
                                </span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('admin.pegawai.edit', $pegawai->nip) }}" class="text-blue-600 hover:text-blue-900 mr-4 inline-flex items-center gap-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.pegawai.destroy', $pegawai->nip) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 inline-flex items-center gap-1" onclick="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 block opacity-50"></i>
                                <p class="text-sm font-medium">Tidak ada data pegawai</p>
                                <p class="text-xs mt-1">Coba ubah filter atau reset untuk melihat semua data</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        {{ $pegawais->appends(request()->query())->links() }}
    </div>
</div>

@endsection