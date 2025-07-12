@props([
    'title' => 'Riwayat Persetujuan',
    'subtitle' => 'Riwayat surat yang telah disetujui atau ditolak',
    'columns' => [],
    'ajaxUrl' => '',
    'showSearch' => true,
    'showEdit' => false,
    'showDelete' => false,
    'pageLength' => 5,
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

                <!-- Filter Section -->
                <div class="grid grid-cols-1 md:grid-cols-8 gap-4 mb-6 p-4 border rounded-lg bg-gray-50">
                    <div class="md:col-span-4">
                        <label for="search_query" class="block text-sm font-medium text-gray-700">Judul / Pengusul</label>
                        <input type="text" id="search_query" name="search_query" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Masukkan kata kunci...">
                    </div>
                    <div class="md:col-span-3">
                        <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status-filter" name="status-filter" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Semua Status</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button id="filter-btn" class="w-full bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-800 transition hover:scale-110 cursor-pointer">Filter</button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <x-datatable 
                        id="riwayat-persetujuan-table"
                        :columns="$columns"
                        :ajaxUrl="$ajaxUrl"
                        :search="false"
                        :ordering="$ordering"
                        :paging="true"
                        :info="true"
                        :lengthMenu="false"
                        :pageLength="$pageLength"
                        :showEdit="$showEdit"
                        :showDelete="$showDelete"
                        :manualInit="true"
                    />
                </div>
            </div>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // Override the default ajax data function to include our custom filters
    var originalAjaxData = window.currentFilterType;
    
    var table = $('#riwayat-persetujuan-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $ajaxUrl }}',
            data: function (d) {
                // Add custom filter parameters
                d.search_query = $('#search_query').val();
                d.status_filter = $('#status-filter').val();
                
                // Handle search value safely
                var searchValue = $('#search_query').val();
                if (searchValue && searchValue.trim() !== '') {
                    d.search = { value: searchValue };
                }
            }
        },
        columns: [
            { data: 'no', name: 'no', orderable: false, searchable: false },
            { data: 'nomor_surat', name: 'nomor_surat' },
            { data: 'judul_surat', name: 'judul_surat' },
            { data: 'tanggal_pengajuan', name: 'tanggal_pengajuan' },
            { data: 'jenis_surat', name: 'jenis_surat' },
            { data: 'pengusul', name: 'pengusul' },
            { 
                data: 'lampiran', 
                name: 'lampiran',
                render: function (data, type, row) {
                    return data ? `<a href="/storage/${data}" target="_blank" class="flex items-center gap-2 text-blue-800 hover:underline">
                        <i class="fa-solid fa-cloud-arrow-up text-gray-500"></i>
                        <span>Lampiran</span>
                    </a>` : '-';
                }
            },
            { data: 'status', name: 'status' }
        ],
        ordering: false,
        lengthChange: false,
        pageLength: 5,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        dom: 'rt<"flex justify-between items-center mt-4"<"text-sm text-gray-600"i><"flex"p>>',
        drawCallback: function (settings) {
            var api = this.api();
            var pageInfo = api.page.info();
            api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + pageInfo.start;
            });
        }
    });

    $('#filter-btn').on('click', function(e) {
        e.preventDefault();
        table.draw();
    });

    // Auto-filter on input change
    $('#search_query, #status-filter').on('change keyup', function() {
        table.draw();
    });
});
</script>
@endsection 