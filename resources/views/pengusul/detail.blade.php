@extends('layouts.app') {{-- Ganti dengan layout yang kamu pakai --}}

@section('title', 'Detail Surat')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Detail Surat</h1>

    <div class="space-y-4">
        <div>
            <strong>Judul Surat:</strong> {{ $surat->judul_surat }}
        </div>

        <div>
            <strong>Tanggal Pengajuan:</strong> {{ $surat->tanggal_pengajuan ?? '-' }}
        </div>

        <div>
            <strong>Jenis Surat:</strong> {{ $surat->jenisSurat->jenis_surat ?? '-' }}
        </div>

        <div>
            <strong>Diajukan Oleh:</strong> {{ $surat->dibuatOleh->nama ?? '-' }}
        </div>

        <div>
            <strong>Diketuai Oleh:</strong> 
            {{ $surat->pengusul->firstWhere('pivot.id_peran_keanggotaan',1)?->nama ?? '' }}
        </div>

        <div>
            <strong>Anggota:</strong> 
            {{ $surat->pengusul->where('pivot.id_peran_keanggotaan', 2)->pluck('nama')->join(', ') ?: '-' }}
        </div>

        <div>
            <strong>Lampiran:</strong> 
            @if($surat->lampiran)
                <a href="{{ asset('storage/' . $surat->lampiran) }}" class="text-blue-600 hover:underline" target="_blank">Unduh Lampiran</a>
            @else
                Tidak ada lampiran
            @endif
        </div>

        <div>
            <strong>Deskripsi:</strong>
            <p class="mt-1 text-gray-700 whitespace-pre-wrap">{{ $surat->deskripsi }}</p>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('mahasiswa.pengajuansurat') }}" class="text-blue-600 hover:underline">← Kembali ke daftar</a>
    </div>
</div>
@endsection
