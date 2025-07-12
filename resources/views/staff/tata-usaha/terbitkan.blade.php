@extends('layouts.app')

@include('components.alertnotif')

@section('title','Terbitkan Surat - Tata Usaha')

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
                    <h1 class="text-2xl font-semibold">Terbitkan Surat</h1>
                    <p class="text-gray-600 mt-1">Halaman untuk menerbitkan surat dari mahasiswa yang telah disetujui.</p>
                </div>
                
                <!-- Data Table Section -->
                <div id="data-table-section" class="mb-8 w-full" style="display: none;">
                    <div class="overflow-x-auto">
                        <x-datatable
                            id="terbitkan-table"
                            :columns="[
                                'DT_RowIndex' => 'No',
                                'judul_surat' => 'Judul Surat',
                                'jenis_surat' => 'Jenis Surat',
                                'mahasiswa' => 'Mahasiswa',
                                'tanggal_pengajuan' => 'Tanggal Pengajuan',
                                'actions' => 'Aksi'
                            ]"
                            ajaxUrl="{{ route('tatausaha.terbitkan.data') }}"
                            :ordering="false"
                            :lengthMenu="false"
                            :manual-init="true"
                            :pageLength="10"
                        />
                    </div>
                </div>

                <!-- Information Section -->
                <div id="info-section" class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-8 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-b from-blue-500 to-blue-600 flex items-center justify-center mb-4 shadow-lg">
                            <i class="fa-solid fa-file-circle-check text-white text-3xl"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-blue-800 mb-3">Belum Ada Surat untuk Diterbitkan</h2>
                        <p class="text-blue-700 max-w-md">
                            Surat yang telah disetujui akan muncul di halaman ini untuk dapat diterbitkan.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#terbitkan-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('tatausaha.terbitkan.data') }}",
            dataSrc: function(json) {
                // Check if there's data
                if (json.data && json.data.length > 0) {
                    $('#data-table-section').show();
                    $('#info-section').hide();
                } else {
                    $('#data-table-section').hide();
                    $('#info-section').show();
                }
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable error:', error);
                $('#data-table-section').hide();
                $('#info-section').show();
                return [];
            }
        },
        columns: [
            { 
                data: null, 
                name: 'DT_RowIndex', 
                orderable: false, 
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'judul_surat', name: 'judul_surat' },
            { data: 'jenis_surat', name: 'jenis_surat' },
            { data: 'mahasiswa', name: 'mahasiswa' },
            { data: 'tanggal_pengajuan', name: 'tanggal_pengajuan' },
            { 
                data: 'actions', 
                name: 'actions', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return data || '';
                }
            }
        ],
        ordering: false,
        lengthChange: false,
        pageLength: 10,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        dom: 'rt<"flex justify-between items-center mt-4"<"text-sm text-gray-600"i><"flex"p>>',
        drawCallback: function(settings) {
            // Check if there's data after drawing
            if (settings.json && settings.json.data && settings.json.data.length > 0) {
                $('#data-table-section').show();
                $('#info-section').hide();
            } else {
                $('#data-table-section').hide();
                $('#info-section').show();
            }
        },
        initComplete: function(settings, json) {
            // Check if there's data after initialization
            if (json && json.data && json.data.length > 0) {
                $('#data-table-section').show();
                $('#info-section').hide();
            } else {
                $('#data-table-section').hide();
                $('#info-section').show();
            }
        }
    });
});
</script>
@endpush
@endsection