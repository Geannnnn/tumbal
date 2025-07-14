<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat PDF</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #222; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .logo { width: 90px; }
        .jenis-surat { font-size: 18px; font-weight: bold; text-align: right; }
        .header-right { text-align: right; }
        .tanggal-terbit { font-size: 12px; color: #666; margin-bottom: 5px; }
        .judul { text-align: center; font-size: 28px; font-weight: bold; margin-bottom: 30px; }
        .meta-table { width: 100%; margin-bottom: 30px; }
        .meta-table td { padding: 4px 0; font-size: 15px; }
        .meta-label { width: 120px; font-weight: 500; }
        .desc-label { font-size: 13px; font-weight: 500; margin-bottom: 2px; }
        .desc-content { font-size: 13px; margin-bottom: 30px; }
        .ttd-container { width: 100%; margin-top: 60px; }
        .ttd { float: right; text-align: center; margin-right: 30px; }
        .ttd .nama { margin-top: 70px; font-weight: bold; }
        .ttd .jabatan { font-size: 13px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('images/Politeknik_Negeri_Batam.png') }}" class="logo">
        <div class="header-right">
            <div class="jenis-surat">{{ $surat->jenisSurat->jenis_surat ?? '-' }}</div>
            <div class="tanggal-terbit">
                {{ $tanggalSurat }}
            </div>
        </div>
    </div>
    <div class="judul">{{ $surat->judul_surat }}</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor surat</td>
            <td>: {{ $surat->nomor_surat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal</td>
            <td>: {{ $tanggalPengajuan }}</td>
        </tr>
        <tr>
            <td class="meta-label">Dari</td>
            <td>: {{ $surat->dibuatOleh->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Perihal</td>
            <td>: {{ $surat->judul_surat }}</td>
        </tr>
    </table>
    <div class="desc-label">Deskripsi</div>
    <div class="desc-content">{{ $surat->deskripsi ?: 'Tidak ada deskripsi' }}</div>
    <div class="ttd-container">
        <div class="ttd">
            <div>Batam, {{ $tanggalSurat }}</div>
            <div class="nama">( ................................. )</div>
            <div class="jabatan">Kepala Sub Bagian</div>
        </div>
    </div>
</body>
</html> 