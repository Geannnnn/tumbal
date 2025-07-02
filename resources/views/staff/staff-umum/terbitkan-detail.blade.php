@extends('layouts.app')

@include('components.alertnotif')

@section('title','Terbitkan Surat - Staff Umum')

@section('content')
<div class="flex h-screen bg-gray-50 ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 p-4 sm:p-6 md:p-8 lg:p-10">
            <div class="container mx-auto">
                @if(!$surat->nomor_surat || optional($surat->statusTerakhir->statusSurat)->status_surat !== 'Diterbitkan')
                <form id="form-terbitkan" action="{{ route('staffumum.terbitkan.surat', $surat->id_surat) }}" method="POST">
                    @csrf
                @endif
                
                <div class="flex justify-end gap-2 mb-6">
                    @if($surat->nomor_surat && optional($surat->statusTerakhir->statusSurat)->status_surat === 'Diterbitkan')
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 text-green-800 rounded-lg">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">Surat Sudah Diterbitkan</span>
                    </div>
                    @else
                    <!-- Terbitkan Button -->
                    <button type="button"
                        onclick="terbitkanSurat(event)"
                        class="cursor-pointer px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 transition-all duration-300 hover:scale-110">
                        Terbitkan
                    </button>
                    @endif
                    
                    <!-- Kembali -->
                    <a href="{{ route('staffumum.terbitkan') }}"
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
                            <span class="col-span-9">: 
                                @if(!$surat->nomor_surat || optional($surat->statusTerakhir->statusSurat)->status_surat !== 'Diterbitkan')
                                    <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat', $surat->nomor_surat) }}" required 
                                           class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent ml-2" 
                                           placeholder="Masukkan nomor surat">
                                @else
                                    {{ $surat->nomor_surat }}
                                @endif
                            </span>
                        </div>
                        <div class="grid grid-cols-12">
                            <span class="col-span-3 font-medium">Tanggal Pengajuan</span>
                            <span class="col-span-9">: {{ $surat->tanggal_pengajuan ? \Carbon\Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d F Y') : '-' }}</span>
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
                            <span class="col-span-3 font-medium">Kepada</span>
                            <span class="col-span-9">: Kepala Sub Bagian</span>
                        </div>
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
                            <p class="mb-16">Batam, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                            <p class="font-semibold">( ....................................... )</p>
                            <p>Staff Umum</p>
                        </div>
                    </div>
                </div>
                
                @if(!$surat->nomor_surat || optional($surat->statusTerakhir->statusSurat)->status_surat !== 'Diterbitkan')
                </form>
                @endif
            </div>
        </main>
    </div>
</div>

<script>
function terbitkanSurat(e) {
    e.preventDefault();
    
    // Validasi nomor surat
    const nomorSurat = document.getElementById('nomor_surat').value.trim();
    if (!nomorSurat) {
        Swal.fire({
            title: 'Error!',
            text: 'Nomor surat harus diisi!',
            icon: 'error',
            confirmButtonColor: '#d33',
        });
        return;
    }
    
    Swal.fire({
        title: 'Terbitkan Surat?',
        text: 'Surat akan diterbitkan dengan nomor: ' + nomorSurat,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Terbitkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-terbitkan').submit();
        }
    });
}
</script>
@endsection 