@extends('layouts.app')

@include('components.alertnotif')

@section('title', 'Tinjau Surat - Staff Umum')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')

        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Tinjau Pengajuan Surat</h1>
                    <p class="text-gray-600 mt-1">Filter dan kelola surat yang masuk.</p>
                </div>
                
                <!-- Filter Section -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 p-4 border rounded-lg bg-gray-50">
                    <div class="md:col-span-5">
                        <label for="search_query" class="block text-sm font-medium text-gray-700">Judul / Ketua / Anggota</label>
                        <input type="text" id="search_query" name="search_query" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Masukkan kata kunci...">
                    </div>
                    <div class="md:col-span-3">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="md:col-span-3">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button id="filter-btn" class="w-full bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-800 transition hover:scale-110 cursor-pointer">Filter</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <x-datatable
                        id="tinjau-surat-table"
                        :columns="[
                            'DT_RowIndex' => 'No',
                            'judul_surat' => 'Judul Surat',
                            'jenis_surat' => 'Jenis Surat',
                            'ketua' => 'Ketua',
                            'tanggal_pengajuan' => 'Tgl. Pengajuan',
                            'status' => 'Status',
                            'actions' => 'Aksi'
                        ]"
                        :ordering="false"
                        :lengthMenu="false"
                        :manual-init="true"
                        :pageLength="5"
                    />
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#tinjau-surat-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('staffumum.tinjau.data') }}",
            data: function (d) {
                d.search_query = $('#search_query').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'judul_surat', name: 'judul_surat' },
            { data: 'jenis_surat', name: 'jenis_surat' },
            { data: 'ketua', name: 'ketua' },
            { data: 'tanggal_pengajuan', name: 'tanggal_pengajuan' },
            { data: 'status', name: 'status' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        ordering: false,
        lengthChange: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        dom: 'rt<"flex justify-between items-center mt-4"<"text-sm text-gray-600"i><"flex"p>>'
    });

    $('#filter-btn').on('click', function(e) {
        e.preventDefault();
        table.draw();
    });
});
</script>
@endpush
@endsection