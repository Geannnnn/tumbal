@extends('layouts.app')

@include('components.alertnotif')

@section('title','Tinjau Surat - Kepala Sub')

@section('content')
<div class="flex h-screen bg-gray-50 ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 p-4 sm:p-6 md:p-8 lg:p-10">
            <div class="container mx-auto">
                <!-- Tombol Aksi di Atas -->
                <div class="flex justify-end items-center gap-3 mb-6">
                    <button type="button" onclick="setujuiSurat()" 
                            class="px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300">
                        Setujui
                    </button>
                    <button type="button" onclick="openTolakModal()" 
                            class="px-5 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:ring-4 focus:outline-none focus:ring-rose-300">
                        Tolak
                    </button>
                    <a href="{{ route('kepalasub.persetujuansurat') }}" 
                       class="px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200">
                        Kembali
                    </a>
                </div>

                <!-- Kontainer Surat -->
                <div class="bg-white max-w-4xl mx-auto p-8 sm:p-12 rounded-lg shadow-md border border-gray-200">
                    <!-- Judul Surat -->
                    <h1 class="text-center text-2xl md:text-3xl font-bold text-gray-800 mb-10 capitalize">
                        {{ $surat->judul_surat }}
                    </h1>

                    <!-- Detail Surat -->
                    <div class="space-y-3 text-gray-700 mb-8">
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Nomor surat</span>
                            <span class="col-span-9">: {{ $surat->nomor_surat ?? '-' }}</span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Tanggal</span>
                            <span class="col-span-9">: {{ \Carbon\Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d-m-Y') }}</span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Dari</span>
                            <span class="col-span-9">: {{ $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-' }}</span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Kepada</span>
                            <span class="col-span-9">: Kepala Sub Bagian</span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Perihal</span>
                            <span class="col-span-9">: {{ $surat->judul_surat }}</span>
                        </div>
                        @if($surat->tujuan_surat)
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Tujuan Surat</span>
                            <span class="col-span-9">: {{ $surat->tujuan_surat }}</span>
                        </div>
                        @endif
                        @if($surat->lampiran)
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Lampiran</span>
                            <span class="col-span-9 flex items-center">: 
                                <a href="/storage/{{ $surat->lampiran }}" target="_blank" class="ml-1 text-indigo-600 hover:underline">
                                    {{ basename($surat->lampiran) }}
                                </a>
                            </span>
                        </div>
                        @endif
                    </div>

                    <!-- Isi Surat -->
                    <div class="prose max-w-none text-gray-800 leading-relaxed text-justify mb-16">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $surat->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                        </div>
                    </div>

                    <!-- Tanda Tangan -->
                    <div class="flex justify-end">
                        <div class="text-center">
                            <p class="mb-24">Batam, {{ \Carbon\Carbon::now()->translatedFormat('d-m-Y') }}</p>
                            <p class="font-semibold">( Kepala Sub Bagian )</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Tolak Surat -->
<div id="modalTolak" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md relative">
        <button onclick="closeTolakModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
        <h3 class="text-lg font-semibold mb-4">Tolak Surat</h3>
        <form id="formTolak" onsubmit="submitTolak(event)">
            <label for="komentar" class="block text-sm font-medium text-gray-700 mb-1">Komentar Penolakan</label>
            <textarea id="komentar" name="komentar" rows="3" class="w-full border border-gray-300 rounded-lg p-2 mb-4" required></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeTolakModal()" class="px-4 py-2 bg-gray-200 rounded-lg text-gray-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700">Kirim</button>
            </div>
        </form>
    </div>
</div>

<script>
function setujuiSurat() {
    Swal.fire({
        title: 'Setujui Surat?',
        text: 'Surat akan diteruskan ke proses penerbitan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('kepala-sub/surat/' . $surat->id_surat . '/approve') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Berhasil' : 'Gagal',
                    text: data.message,
                }).then(() => {
                    if (data.success) window.location.href = '{{ route('kepalasub.persetujuansurat') }}';
                });
            })
            .catch(() => {
                Swal.fire('Gagal', 'Terjadi kesalahan server', 'error');
            });
        }
    });
}

function openTolakModal() {
    document.getElementById('modalTolak').classList.remove('hidden');
}
function closeTolakModal() {
    document.getElementById('modalTolak').classList.add('hidden');
}
function submitTolak(e) {
    e.preventDefault();
    const komentar = document.getElementById('komentar').value;
    Swal.fire({
        title: 'Tolak Surat?',
        text: 'Surat akan ditolak dan komentar akan dikirim.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Tolak',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('kepala-sub/surat/' . $surat->id_surat . '/reject') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ komentar })
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.success ? 'success' : 'error',
                    title: data.success ? 'Berhasil' : 'Gagal',
                    text: data.message,
                }).then(() => {
                    if (data.success) window.location.href = '{{ route('kepalasub.persetujuansurat') }}';
                });
            })
            .catch(() => {
                Swal.fire('Gagal', 'Terjadi kesalahan server', 'error');
            });
        }
    });
}
</script>
@endsection 