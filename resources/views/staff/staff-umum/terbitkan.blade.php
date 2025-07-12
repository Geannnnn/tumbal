@extends('layouts.app')

@include('components.alertnotif')

@section('title','Terbitkan Surat - Staff Umum')

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-gray-50 p-4 sm:p-6 md:p-8 lg:p-10">
            <div class="max-w-full mx-auto">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Terbitkan Surat</h1>
                <p class="text-gray-600 mb-8">Halaman untuk menerbitkan surat dari dosen yang telah disetujui.</p>
                
                <!-- Data Table Section -->
                <div id="data-table-section" class="mb-8 w-full" style="display: none;">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Surat Menunggu Penerbitan</h2>
                        <div class="overflow-x-auto w-full">
                            <table id="terbitkan-table" class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Surat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Surat</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosen</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Information Section -->
                <div id="info-section" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-blue-800 mb-4">Informasi</h2>
                    <p class="text-blue-700">
                        Surat yang telah disetujui akan dapat diterbitkan melalui halaman ini.
                    </p>
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
            url: "{{ route('staffumum.terbitkan.data') }}",
            dataSrc: function(json) {
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
            { data: 'dosen', name: 'dosen' },
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
            if (settings.json && settings.json.data && settings.json.data.length > 0) {
                $('#data-table-section').show();
                $('#info-section').hide();
            } else {
                $('#data-table-section').hide();
                $('#info-section').show();
            }
        },
        initComplete: function(settings, json) {
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