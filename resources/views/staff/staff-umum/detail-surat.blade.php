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
                <div class="flex justify-end gap-2 mb-6">
                    <!-- Approve Form -->
                    <form id="form-approve" action="{{ route('staffumum.surat.approve', $surat->id_surat) }}" method="POST" class="inline">
                        @csrf
                        <button type="button"
                            onclick="approveSurat(event)"
                            class="cursor-pointer px-5 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 transition-all duration-300 hover:scale-110">
                            Approve
                        </button>
                    </form>

                    <!-- Tolak Button (open modal) -->
                    <button type="button" onclick="openTolakModal()"
                        class="cursor-pointer px-5 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:ring-4 focus:outline-none focus:ring-rose-300 transition-all duration-300 hover:scale-110">
                        Tolak
                    </button>

                    <!-- Kembali -->
                    <a href="{{ route('staffumum.tinjausurat') }}"
                    class="inline-block px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 transition-all duration-300 hover:scale-110">
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
                            <span class="col-span-3 font-medium">Tanggal Pengajuan</span>
                            <span class="col-span-9">: {{ \Carbon\Carbon::parse($surat->tanggal_pengajuan)->locale('id')->translatedFormat('d-m-Y') }}</span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Dari</span>
                            <span class="col-span-9">: {{ $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-' }}</span>
                        </div>
                        @if($surat->tujuan_surat)
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Tujuan Surat</span>
                            <span class="col-span-9">: {{ $surat->tujuan_surat }}</span>
                        </div>
                        @endif
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Perihal</span>
                            <span class="col-span-9">: {{ $surat->judul_surat }}</span>
                        </div>
                        
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
                            <p class="mb-24">Batam, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d-m-Y') }}</p>
                            <p class="font-semibold">( Kepala Sub Bagian )</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Tolak -->
<div id="modalTolak" class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-40 backdrop-blur-sm hidden transition-opacity duration-300 ease-in-out">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">Tolak Surat</h2>
        <form id="form-tolak" action="{{ route('staffumum.surat.tolak', $surat->id_surat) }}" method="POST">
            @csrf
            <textarea name="komentar" required class="w-full border rounded p-2 mb-4" rows="4" placeholder="Tulis alasan penolakan..."></textarea>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeTolakModal()" class="cursor-pointer px-5 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg focus:ring-4 focus:outline-none focus:ring-rose-300 transition-all duration-300 hover:scale-110">Batal</button>
                <button type="submit" onclick="this.disabled=true;this.form.submit()" class="cursor-pointer px-5 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:ring-4 focus:outline-none focus:ring-rose-300 transition-all duration-300 hover:scale-110">Kirim</button>
            </div>
        </form>
    </div>
</div>

<script>
function approveSurat(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Setujui Surat?',
        text: 'Surat akan diteruskan ke Kepala Sub untuk proses selanjutnya.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-approve').submit();
        }
    });
}

function tolakSurat() {
    if (confirm('Apakah Anda yakin ingin menolak surat ini?')) {
        // Implementasi logika penolakan
        // Anda bisa menambahkan form submit di sini jika perlu
        alert('Surat berhasil ditolak!');
        window.location.href = '{{ route("kepalasub.persetujuansurat") }}';
    }
}

function openTolakModal() {
    document.getElementById('modalTolak').classList.remove('hidden');
}

function closeTolakModal() {
    document.getElementById('modalTolak').classList.add('hidden');
}
</script>
@endsection 