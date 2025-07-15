@extends('layouts.app')

@include('components.alertnotif')

@section('title','Terbitkan Surat - Tata Usaha')

@section('content')
<div class="flex h-screen bg-gray-50 ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 p-4 sm:p-6 md:p-8 lg:p-10">
            <div class="container mx-auto">
                @if(!$surat->nomor_surat || optional($surat->statusTerakhir->statusSurat)->status_surat !== 'Diterbitkan')
                <form id="form-terbitkan" action="{{ route('tatausaha.surat.terbitkan', $surat->id_surat) }}" method="POST">
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
                    <!-- Tombol Terbitkan -->
                    <button type="button"
                        onclick="konfirmasiTerbitkan()"
                        class="cursor-pointer px-5 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 transition-all duration-300 hover:scale-110">
                        Terbitkan
                    </button>
                    @endif
                    
                    <!-- Kembali -->
                    <a href="{{ route('tatausaha.terbitkan') }}"
                    class="inline-block px-5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 transition-all duration-300 hover:scale-110">
                        Kembali
                    </a>
                </div>

                <!-- Kontainer Surat -->
                <div class="bg-white max-w-4xl mx-auto p-8 sm:p-12 rounded-lg shadow-md border border-gray-200">
                    {{-- Header Surat --}}
                    <div class="text-center mb-6 flex items-center">
                        <img src="{{ asset('images/poltek.png') }}" alt="Logo Polibatam" class="mb-2 mr-4" style="height: 90px;">
                        <div class="flex-1">
                            <div class="font-bold text-lg">KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</div>
                            <div class="font-bold text-lg">POLITEKNIK NEGERI BATAM</div>
                            <div class="text-sm">Jalan Ahmad Yani, Batam Centre, Kecamatan Batam Kota, Batam 29461</div>
                            <div class="text-sm">Telepon +62 778 469856 - 469860, Faksimile +62 778 463620</div>
                            <div class="text-sm">Laman: www.polibatam.ac.id, Surel: info@polibatam.ac.id</div>
                            <hr class="my-2 border-t-2 border-black">
                        </div>
                    </div>

                    {{-- Judul dan Nomor Surat --}}
                    <div class="text-center mb-4">
                        <div class="font-bold text-xl uppercase">{{ $surat->judul_surat }}</div>
                        <div class="flex justify-center items-center mt-2">
                            <span class="font-medium">Nomor:</span>
                            @if($surat->nomor_surat && optional($surat->statusTerakhir->statusSurat)->status_surat === 'Diterbitkan')
                                <span class="ml-3">{{ $surat->nomor_surat }}</span>
                            @else
                                <span class="ml-3 text-gray-400 italic">(Akan digenerate otomatis saat diterbitkan)</span>
                            @endif
                        </div>
                    </div>

                    {{-- Detail Surat --}}
                    <div class="mb-6">
                        <div class="grid grid-cols-12 mb-2">
                            <span class="col-span-3 font-medium">Dari</span>
                            <span class="col-span-9">: {{ $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-' }}</span>
                        </div>
                        <div class="grid grid-cols-12 mb-2">
                            <span class="col-span-3 font-medium">Tanggal Pengajuan</span>
                            <span class="col-span-9">: {{ $surat->tanggal_pengajuan ? \Carbon\Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d-m-Y') : '-' }}</span>
                        </div>
                        @if($surat->tujuan_surat)
                        <div class="grid grid-cols-12 mb-2">
                            <span class="col-span-3 font-medium">Tujuan Surat</span>
                            <span class="col-span-9">: {{ $surat->tujuan_surat }}</span>
                        </div>
                        @endif
                        <div class="grid grid-cols-12 mb-2">
                            <span class="col-span-3 font-medium">Perihal</span>
                            <span class="col-span-9">: {{ $surat->judul_surat }}</span>
                        </div>
                        @if($surat->lampiran)
                        <div class="grid grid-cols-12 mb-2">
                            <span class="col-span-3 font-medium">Lampiran</span>
                            <span class="col-span-9 flex items-center">: 
                                <a href="/storage/{{ $surat->lampiran }}" target="_blank" class="ml-1 text-indigo-600 hover:underline">
                                    {{ basename($surat->lampiran) }}
                                </a>
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Isi Surat --}}
                    <div class="prose max-w-none text-gray-800 leading-relaxed text-justify mb-16">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $surat->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                        </div>
                    </div>
                    
                    
                    {{-- Nama Anggota --}}
                    @if($surat->pengusul && count($surat->pengusul))
                    <div class="mb-6">
                        <div class="font-medium mb-2">Nama Anggota:</div>
                        <table class="w-full border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-2 py-1">No</th>
                                    <th class="border px-2 py-1">Nama</th>
                                    <th class="border px-2 py-1">NIM/NIP</th>
                                    <th class="border px-2 py-1">Jabatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($surat->pengusul as $i => $anggota)
                                <tr>
                                    <td class="border px-2 py-1 text-center">{{ $i+1 }}</td>
                                    <td class="border px-2 py-1">{{ $anggota->nama }}</td>
                                    <td class="border px-2 py-1">{{ $anggota->nim ?? $anggota->nip ?? '-' }}</td>
                                    <td class="border px-2 py-1">
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
                    </div>
                    @endif

                    {{-- Tanda Tangan & QR --}}
                    <div class="flex justify-end mt-12">
                        <div class="text-center">
                            <p class="mb-4">Batam, {{ \Carbon\Carbon::now()->translatedFormat('d-m-Y') }}</p>
                            <!-- QR Code Verifikasi -->
                            <div class="mb-4 text-center">
                                <img class="mx-auto" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode('https://tte.polibatam.ac.id/index.php?page=qrsign&id=UWtZZ1RNUkdldFU9') }}" alt="QR Verifikasi TTE">
                                <p class="text-xs text-gray-500 mt-2">Link: <a href="https://tte.polibatam.ac.id/index.php?page=qrsign&id=UWtZZ1RNUkdldFU9" target="_blank" class="underline text-blue-600">Verifikasi TTE</a></p>
                            </div>
                            <p class="font-semibold">( Kepala Sub )</p>
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

@endsection

<script>
function konfirmasiTerbitkan() {
    Swal.fire({
        title: 'Terbitkan Surat?',
        text: 'Nomor surat akan digenerate otomatis dan surat akan diterbitkan.',
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
