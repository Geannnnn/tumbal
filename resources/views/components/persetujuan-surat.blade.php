@props([
    'title' => 'Persetujuan Surat',
    'subtitle' => 'Kelola surat yang menunggu persetujuan',
    'columns' => [],
    'ajaxUrl' => '',
    'showSearch' => true,
    'showEdit' => false,
    'showDelete' => false,
    'pageLength' => 10,
    'ordering' => false
])

@extends('layouts.app')

@include('components.alertnotif')

@section('title', $title)

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Header Section -->
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">{{ $title }}</h1>
                    <p class="text-gray-600 mt-1">{{ $subtitle }}</p>
                </div>

                <!-- Data Table -->
                <div class="bg-white rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Daftar Surat Menunggu Persetujuan</h2>
                        <p class="text-sm text-gray-600 mt-1">Surat yang perlu disetujui atau ditolak</p>
                    </div>
                    <div class="p-6">
                        <x-datatable 
                            :columns="$columns" 
                            :url="$ajaxUrl"
                            :manual-init="true"
                            :ordering="$ordering"
                            :length-menu="false"
                            :page-length="$pageLength"
                        />
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    const table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $ajaxUrl }}',
            type: 'GET'
        },
        columns: [
            { data: 'no', name: 'no', orderable: false, searchable: false },
            { data: 'nomor_surat', name: 'nomor_surat' },
            { data: 'judul_surat', name: 'judul_surat' },
            { data: 'tanggal_pengajuan', name: 'tanggal_pengajuan' },
            { data: 'jenis_surat', name: 'jenis_surat' },
            { data: 'pengusul', name: 'pengusul' },
            { data: 'lampiran', name: 'lampiran' },
            { data: 'status', name: 'status' }
        ],
        order: [[3, 'desc']], // Sort by tanggal_pengajuan descending
        pageLength: {{ $pageLength }},
        lengthMenu: false,
        searching: true,
        info: true,
        responsive: true
    });
});
</script>
@endsection 