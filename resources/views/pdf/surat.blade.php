<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat PDF</title>
    <style>
        @page { size: A4; margin: 30px 40px 30px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #222; margin: 0; }
        .header-flex { display: flex; align-items: flex-start; margin-bottom: 0; }
        .logo { width: 100px; margin-right: 24px; }
        .header-text { flex: 1; }
        .header-text .title { font-weight: bold; font-size: 18px; margin-bottom: 2px; }
        .header-text .subtitle { font-weight: bold; font-size: 18px; margin-bottom: 2px; }
        .header-text .info { font-size: 13px; margin-bottom: 0; }
        .header-hr { border: none; border-top: 2px solid #000; margin: 10px 0 18px 0; }
        .jenis-surat { font-size: 16px; font-weight: bold; margin-top: 10px; margin-bottom: 0; }
        .nomor-surat { font-size: 15px; font-weight: bold; text-align: center; margin: 0; }
        .judul { text-align: center; font-size: 22px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
        .meta-table { width: 100%; margin-bottom: 20px; font-size: 15px; }
        .meta-table td { padding: 4px 0; }
        .meta-label { width: 120px; font-weight: 500; }
        .desc-label { font-size: 13px; font-weight: 500; margin-bottom: 2px; }
        .desc-content { font-size: 13px; margin-bottom: 30px; }
        .anggota-table { width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 14px; }
        .anggota-table th, .anggota-table td { border: 1px solid #888; padding: 6px 8px; text-align: center; }
        .anggota-table th { background: #f3f3f3; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <!-- HEADER HALAMAN PERTAMA -->
    <table width="100%" style="margin-bottom:0;">
        <tr>
            <td style="width:130px; vertical-align:top;">
                <img src="{{ public_path('images/poltek.png') }}" style="width:120px; margin-bottom:8px;">
            </td>
            <td style="vertical-align:top; padding-left:10px;">
                <div style="font-weight:bold; font-size:19px; letter-spacing:0.5px; text-align:center;">
                    KEMENTERIAN PENDIDIKAN TINGGI,<br>
                    SAINS, DAN TEKNOLOGI
                </div>
                <div style="font-weight:bold; font-size:19px; margin-bottom:2px; text-align:center;">
                    POLITEKNIK NEGERI BATAM
                </div>
                <div style="font-size:13px; text-align:center;">
                    Jalan Ahmad Yani, Batam Centre, Kecamatan Batam Kota, Batam 29461
                </div>
                <div style="font-size:13px; text-align:center;">
                    Telepon +62 778 469856 - 469860, Faksimile +62 778 463620
                </div>
                <div style="font-size:13px; text-align:center;">
                    Laman: www.polibatam.ac.id, Surel: info@polibatam.ac.id
                </div>
            </td>
        </tr>
    </table>
    <hr style="border: none; border-top: 2.5px solid #000; margin: 8px 0 18px 0;">
    <!-- JUDUL SURAT -->
    <div class="judul">{{ $surat->judul_surat }}</div>
    @if($surat->nomor_surat)
    <div style="text-align: center; font-size: 13px; margin-top: -10px; margin-bottom: 20px;">
        Nomor Surat: {{ $surat->nomor_surat }}
    </div>
    @endif
    <table class="meta-table">
        <tr>
            <td class="meta-label">Tanggal</td>
            <td>: {{ $tanggalPengajuan }}</td>
        </tr>
        <tr>
            <td class="meta-label">Dari</td>
            <td>: {{ $surat->dibuatOleh->nama ?? '-' }}</td>
        </tr>
        @if($surat->tujuan_surat)
        <tr>
            <td class="meta-label">Tujuan Surat</td>
            <td>: {{ $surat->tujuan_surat }}</td>
        </tr>
        @endif
        <tr>
            <td class="meta-label">Perihal</td>
            <td>: {{ $surat->judul_surat }}</td>
        </tr>
        @if($surat->lampiran)
        <tr>
            <td class="meta-label">Lampiran</td>
            <td>: {{ basename($surat->lampiran) }}</td>
        </tr>
        @endif
    </table>
    <div class="desc-label">Deskripsi</div>
    <div class="desc-content">{{ $surat->deskripsi ?: 'Tidak ada deskripsi' }}</div>

    <!-- FOOTER TANDA TANGAN HANYA DI HALAMAN PERTAMA -->
    <table width="100%" style="margin-top: 80px;">
        <tr>
            <td style="width: 50%; text-align: left;">
                <div style="margin-bottom: 4px;">Mengetahui,</div>
                <div style="margin-bottom: 4px;">Pengaju</div>
                <div style="height: 100px;">
                    <img src="{{ public_path('storage/qr_pengaju.png') }}" style="width:100px;">
                </div>
                <div style="margin-top: 12px; font-weight: bold; text-decoration: underline;">
                    {{ $surat->dibuatOleh->nama ?? '-' }}
                </div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div style="margin-bottom: 4px;">Batam, {{ $tanggalSurat }}</div>
                <div style="margin-bottom: 4px;">Kepala Sub Bagian</div>
                <div style="height: 100px;">
                    <img src="{{ public_path('storage/qr_kepalasub.png') }}" style="width:100px; pr-10">
                </div>
                <div style="margin-top: 12px; font-weight: bold; text-decoration: underline;">
                    Kepala Sub Bagian Umum 
                </div>
            </td>
        </tr>
    </table>

    @if($surat->pengusul && count($surat->pengusul))
        <div class="page-break"></div>
        <!-- HEADER HALAMAN KEDUA (SAMA DENGAN PERTAMA) -->
        <table width="100%" style="margin-bottom:0;">
            <tr>
                <td style="width:130px; vertical-align:top;">
                    <img src="{{ public_path('images/poltek.png') }}" style="width:120px; margin-bottom:8px;">
                </td>
                <td style="vertical-align:top; padding-left:10px;">
                    <div style="font-weight:bold; font-size:19px; letter-spacing:0.5px; text-align:center;">
                        KEMENTERIAN PENDIDIKAN TINGGI,<br>
                        SAINS, DAN TEKNOLOGI
                    </div>
                    <div style="font-weight:bold; font-size:19px; margin-bottom:2px; text-align:center;">
                        POLITEKNIK NEGERI BATAM
                    </div>
                    <div style="font-size:13px; text-align:center;">
                        Jalan Ahmad Yani, Batam Centre, Kecamatan Batam Kota, Batam 29461
                    </div>
                    <div style="font-size:13px; text-align:center;">
                        Telepon +62 778 469856 - 469860, Faksimile +62 778 463620
                    </div>
                    <div style="font-size:13px; text-align:center;">
                        Laman: www.polibatam.ac.id, Surel: info@polibatam.ac.id
                    </div>
                </td>
            </tr>
        </table>
        <hr style="border: none; border-top: 2.5px solid #000; margin: 8px 0 18px 0;">
        <div class="judul" style="margin-top: 30px;">Daftar Ketua & Anggota</div>
        <table class="anggota-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM/NIP</th>
                    <th>Peran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surat->pengusul as $i => $anggota)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $anggota->nama }}</td>
                    <td>{{ $anggota->nim ?? $anggota->nip ?? '-' }}</td>
                    <td>
                        @if($anggota->pivot->id_peran_keanggotaan == 1)
                            Ketua
                        @elseif($anggota->pivot->id_peran_keanggotaan == 2)
                            Anggota
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html> 