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
                @forelse($pegawais as $pegawai)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $pegawai->nip }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $pegawai->nama_pegawai }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $pegawai->divisi->nama_divisi ?? 'N/A' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $pegawai->jabatan }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                        <a href="{{ route('admin.pegawai.edit', $pegawai->nip) }}" class="text-blue-600 hover:text-blue-900 mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.pegawai.destroy', $pegawai->nip) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">Belum ada data pegawai.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $pegawais->links() }}
    </div>
</div>

@endsection