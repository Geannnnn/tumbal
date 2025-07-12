@extends('layouts.app')

@include('components.alertnotif')

@section('title','Tinjau Surat - Direktur')

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Header Section -->
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Tinjau Surat</h1>
                    <p class="text-gray-600 mt-1">Tinjau detail surat sebelum memberikan persetujuan</p>
                </div>

                <!-- Surat Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Surat Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold mb-4">Informasi Surat</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $surat->nomor_surat ?? '-' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Judul Surat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $surat->judul_surat ?? '-' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Jenis Surat</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $surat->jenisSurat->jenis_surat ?? '-' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tanggal Pengajuan</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $surat->tanggal_pengajuan ? date('d-m-Y', strtotime($surat->tanggal_pengajuan)) : '-' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Pengusul</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $surat->dibuatOleh->nama ?? '-' }}</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Lampiran</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $surat->lampiran ?? '-' }}</p>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700">Isi Surat</label>
                                <div class="mt-1 p-4 bg-gray-50 rounded-md">
                                    <p class="text-sm text-gray-900">{{ $surat->isi_surat ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Panel -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold mb-4">Aksi</h2>
                            
                            <div class="space-y-4">
                                <button onclick="approveSurat()" class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Setujui Surat
                                </button>
                                
                                <button onclick="rejectSurat()" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Tolak Surat
                                </button>
                            </div>
                        </div>

                        <!-- Status History -->
                        <div class="bg-white rounded-lg border border-gray-200 p-6 mt-6">
                            <h2 class="text-lg font-semibold mb-4">Riwayat Status</h2>
                            
                            <div class="space-y-3">
                                @foreach($surat->riwayatStatus as $riwayat)
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $riwayat->statusSurat->status_surat }}</p>
                                        <p class="text-xs text-gray-500">{{ $riwayat->tanggal_rilis ? date('d-m-Y H:i', strtotime($riwayat->tanggal_rilis)) : '-' }}</p>
                                        @if($riwayat->keterangan)
                                        <p class="text-xs text-gray-600 mt-1">{{ $riwayat->keterangan }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal for Approve/Reject -->
<div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 id="modalTitle" class="text-lg font-medium text-gray-900 mb-4"></h3>
            <div class="mt-2">
                <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                <textarea id="keterangan" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Batal
                </button>
                <button id="confirmButton" class="px-4 py-2 text-white rounded-md">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAction = '';

function approveSurat() {
    currentAction = 'approve';
    document.getElementById('modalTitle').textContent = 'Setujui Surat';
    document.getElementById('confirmButton').textContent = 'Setujui';
    document.getElementById('confirmButton').className = 'px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700';
    document.getElementById('actionModal').classList.remove('hidden');
}

function rejectSurat() {
    currentAction = 'reject';
    document.getElementById('modalTitle').textContent = 'Tolak Surat';
    document.getElementById('confirmButton').textContent = 'Tolak';
    document.getElementById('confirmButton').className = 'px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700';
    document.getElementById('actionModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('actionModal').classList.add('hidden');
    document.getElementById('keterangan').value = '';
}

document.getElementById('confirmButton').addEventListener('click', function() {
    const keterangan = document.getElementById('keterangan').value;
    const url = currentAction === 'approve' 
        ? '{{ route("direktur.approve", $surat->id_surat) }}'
        : '{{ route("direktur.reject", $surat->id_surat) }}';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            keterangan: keterangan
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("direktur.persetujuansurat") }}';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memproses permintaan');
    });
    
    closeModal();
});
</script>
@endsection 