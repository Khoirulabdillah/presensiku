<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Export Presensi</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px; }
    th { background: #f3f4f6; }
    .center { text-align: center; }
  </style>
</head>
<body>
  <h3 style="margin-bottom:6px;">Laporan Presensi</h3>
  <p style="margin-top:0;margin-bottom:8px;">Tanggal: {{ now()->format('d/m/Y H:i') }}</p>

  <table>
    <thead>
      <tr>
        <th>NIP</th>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Type</th>
        <th>Status</th>
        <th>Jam Masuk</th>
        <th>Jam Pulang</th>
        <th>Latitude</th>
        <th>Longitude</th>
      </tr>
    </thead>
    <tbody>
      @foreach($presensis as $p)
      <tr>
        <td>{{ $p->nip }}</td>
        <td>{{ $p->pegawai->nama_pegawai ?? '-' }}</td>
        <td>{{ $p->tanggal_presensi?->format('d/m/Y') ?? '-' }}</td>
        <td>{{ ucfirst($p->type ?? '-') }}</td>
        <td>{{ $p->status ?? '-' }}</td>
        <td>{{ $p->jam_masuk ?? '-' }}</td>
        <td>{{ $p->jam_pulang ?? '-' }}</td>
        <td>{{ $p->latitude ?? '-' }}</td>
        <td>{{ $p->longitude ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</body>
</html>
