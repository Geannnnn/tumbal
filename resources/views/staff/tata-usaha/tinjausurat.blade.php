@extends('layouts.app')

@include('components.alertnotif')

@section('title', 'Tinjau Surat - Tata Usaha')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])

        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Tinjau Pengajuan Surat</h1>
                    <p class="text-gray-600 mt-1">Filter dan kelola surat yang masuk dari mahasiswa.</p>
                </div>
                
                <!-- Filter Section -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 p-4 border rounded-lg bg-gray-50">
                    <div class="md:col-span-5">
                        <label for="search_query" class="block text-sm font-medium text-gray-700">Judul / Mahasiswa</label>
                        <input type="text" id="search_query" name="search_query" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Masukkan kata kunci...">
                    </div>
                    <div class="md:col-span-6 flex items-end space-x-2">
                        <div class="flex-1">
                            <label for="periode" class="block text-sm font-medium text-gray-700">Periode</label>
                            <input type="text" id="periode" name="periode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Pilih rentang tanggal">
                            <input type="hidden" id="periode_awal" name="periode_awal">
                            <input type="hidden" id="periode_akhir" name="periode_akhir">
                        </div>
                        <button id="reset-filter-btn" type="button" class="ml-2 mb-1 px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold flex items-center" title="Reset Filter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
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
<!-- Litepicker CSS & JS CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">
<script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
<script>
$(document).ready(function() {
    // Inisialisasi Litepicker
    const picker = new Litepicker({
        element: document.getElementById('periode'),
        singleMode: false,
        format: 'DD-MM-YYYY',
        lang: 'id-ID',
        autoApply: true,
        setup: (picker) => {
            picker.on('selected', (start, end) => {
                $('#periode_awal').val(start ? start.format('YYYY-MM-DD') : '');
                $('#periode_akhir').val(end ? end.format('YYYY-MM-DD') : '');
            });
        }
    });

    var table = $('#tinjau-surat-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('tatausaha.tinjau.data') }}",
            data: function (d) {
                d.search_query = $('#search_query').val();
                d.periode_awal = $('#periode_awal').val();
                d.periode_akhir = $('#periode_akhir').val();
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
        pageLength: 5,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        dom: 'rt<"flex justify-between items-center mt-4"<"text-sm text-gray-600"i><"flex"p>>'
    });

    $('#filter-btn').on('click', function(e) {
        e.preventDefault();
        table.draw();
    });
    $('#reset-filter-btn').on('click', function() {
        $('#periode').val('');
        $('#periode_awal').val('');
        $('#periode_akhir').val('');
        $('#search_query').val('');
        table.draw();
    });
});
</script>
@endpush
@endsection