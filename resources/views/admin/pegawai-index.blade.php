<x-headeradmin>

<div class="p-6">

    <!-- HEADER DAN TOMBOL TAMBAH -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-extrabold text-gray-800">
            <i class="fas fa-users mr-3 text-blue-700"></i> Data Pegawai
        </h2>
        <a href="{{ route('admin.pegawai.create') }}" 
           class="bg-blue-800 text-white font-semibold px-4 py-2 rounded-lg shadow-md hover:bg-blue-700 transition duration-200">
            <i class="fas fa-plus mr-1"></i> Tambah Pegawai
        </a>
    </div>

    <!-- Notifikasi Sukses -->
    @if (session('success'))
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-lg" role="alert">
            <p class="font-bold">Berhasil!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <!-- CARD TABEL -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden">
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-blue-900 text-white">
                        <th class="p-4 text-left font-semibold text-sm uppercase tracking-wider rounded-tl-xl">NIP</th>
                        <th class="p-4 text-left font-semibold text-sm uppercase tracking-wider">Nama</th>
                        <th class="p-4 text-left font-semibold text-sm uppercase tracking-wider">Jabatan</th>
                        <th class="p-4 text-left font-semibold text-sm uppercase tracking-wider">Divisi ID</th>
                        <th class="p-4 text-center font-semibold text-sm uppercase tracking-wider rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @forelse ($pegawai as $p)
                        <tr class="hover:bg-blue-50 transition duration-150 ease-in-out">
                            <td class="p-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $p->nip }}</td>
                            <td class="p-4 whitespace-nowrap text-sm text-gray-700">{{ $p->nama_pegawai }}</td>
                            <td class="p-4 whitespace-nowrap text-sm text-gray-700">{{ $p->jabatan }}</td>
                            <td class="p-4 whitespace-nowrap text-sm text-gray-700">{{ $p->divisi->nama_divisi ?? $p->divisi_id }}</td>
                            <td class="p-4 whitespace-nowrap text-sm text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    
                                    <!-- Tombol Edit -->
                                    <a href="{{ route('admin.pegawai.edit', $p->nip) }}"
                                       class="text-blue-600 hover:text-blue-900 p-2 rounded-full transition duration-150 ease-in-out bg-blue-100 hover:bg-blue-200"
                                       title="Edit Data">
                                        <i class="fas fa-edit fa-fw"></i>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.pegawai.destroy', $p->nip) }}" method="POST"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pegawai {{ $p->nama_pegawai }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 p-2 rounded-full transition duration-150 ease-in-out bg-red-100 hover:bg-red-200"
                                                title="Hapus Data">
                                            <i class="fas fa-trash-alt fa-fw"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500 text-lg">
                                Belum ada data pegawai yang ditambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
    </div>

</div>

</x-headeradmin>