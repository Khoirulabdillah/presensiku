@extends('layouts.app')

@section('title', 'Detail Permohonan Izin')

@section('content')

<div class="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">

    <div class="max-w-4xl mx-auto">

        <div class="bg-white rounded-xl shadow-2xl overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-file-alt text-white text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-white">Detail Permohonan Izin</h1>
                    </div>
                    <a href="{{ route('admin.izin.index') }}"
                       class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">

                <!-- Status Badge -->
                <div class="mb-6">
                    @if($izin->status_izin == 'pending')
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-clock mr-2"></i>Status: Pending
                        </span>
                    @elseif($izin->status_izin == 'approved')
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            <i class="fas fa-check mr-2"></i>Status: Approved
                        </span>
                    @elseif($izin->status_izin == 'rejected')
                        <span class="inline-flex px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            <i class="fas fa-times mr-2"></i>Status: Rejected
                        </span>
                    @endif
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                    <!-- Pegawai Info -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Informasi Pegawai
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Nama Pegawai</label>
                                <p class="text-sm text-gray-900">{{ $izin->pegawai->nama_pegawai ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">NIP</label>
                                <p class="text-sm text-gray-900">{{ $izin->nip }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Jabatan</label>
                                <p class="text-sm text-gray-900">{{ $izin->pegawai->jabatan ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Divisi</label>
                                <p class="text-sm text-gray-900">{{ $izin->pegawai->divisi->nama_divisi ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Izin Info -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Informasi Izin
                        </h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Jenis Izin</label>
                                <p class="text-sm text-gray-900">{{ $izin->jenis_izin }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Tanggal Mulai</label>
                                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d F Y') }}</p>
                            </div>
                            @if($izin->tanggal_selesai)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600">Tanggal Selesai</label>
                                    <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d F Y') }}</p>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-600">Tanggal Pengajuan</label>
                                <p class="text-sm text-gray-900">{{ $izin->created_at->format('d F Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Keterangan -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-comment mr-2 text-blue-600"></i>Keterangan
                    </h3>
                    <p class="text-sm text-gray-900 whitespace-pre-line">{{ $izin->keterangan }}</p>
                </div>

                <!-- Bukti -->
                @if($izin->bukti_path)
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-file mr-2 text-blue-600"></i>Bukti Pendukung
                        </h3>
                        <div class="flex items-center space-x-4">
                            <a href="{{ Storage::url($izin->bukti_path) }}"
                               target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-download mr-2"></i>Lihat/Download Bukti
                            </a>
                            @php
                                $extension = pathinfo($izin->bukti_path, PATHINFO_EXTENSION);
                            @endphp
                            @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                                <span class="text-sm text-gray-600">
                                    <i class="fas fa-image mr-1"></i>File Gambar
                                </span>
                            @elseif(strtolower($extension) == 'pdf')
                                <span class="text-sm text-gray-600">
                                    <i class="fas fa-file-pdf mr-1"></i>File PDF
                                </span>
                            @else
                                <span class="text-sm text-gray-600">
                                    <i class="fas fa-file mr-1"></i>File {{ strtoupper($extension) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Catatan Admin -->
                @if($izin->catatan_admin)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                            <i class="fas fa-sticky-note mr-2"></i>Catatan Admin
                        </h3>
                        <p class="text-sm text-blue-900 whitespace-pre-line">{{ $izin->catatan_admin }}</p>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    {{-- Quick Approve --}}
                    <form action="{{ route('admin.izin.update', $izin->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_izin" value="approved" />
                        <input type="hidden" name="catatan_admin" value="" />
                        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition duration-200"
                                onclick="return confirm('Setujui permohonan izin ini?')">
                            <i class="fas fa-check mr-2"></i>Approve
                        </button>
                    </form>

                    {{-- Quick Reject (asks for note) --}}
                    <form action="{{ route('admin.izin.update', $izin->id) }}" method="POST" class="inline reject-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_izin" value="rejected" />
                        <input type="hidden" name="catatan_admin" value="" class="catatan-input" />
                        <button type="button" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition duration-200 reject-btn">
                            <i class="fas fa-times mr-2"></i>Reject
                        </button>
                    </form>

                    <form action="{{ route('admin.izin.destroy', $izin->id) }}" method="POST" class="inline"
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan izin ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition duration-200">
                            <i class="fas fa-trash mr-2"></i>Hapus
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.reject-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const form = btn.closest('.reject-form');
                        let note = prompt('Masukkan catatan atau alasan penolakan (opsional):');
                        if (note === null) return; // cancel
                        const input = form.querySelector('.catatan-input');
                        if (input) input.value = note;
                        if (confirm('Konfirmasi: tolak permohonan ini?')) {
                            form.submit();
                        }
                    });
                });
            });
            </script>

            @endsection