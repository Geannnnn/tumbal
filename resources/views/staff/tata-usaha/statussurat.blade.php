@extends('layouts.app')

@include('components.alertnotif')

@section('title','Status Surat - Tata Usaha')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="title-page flex justify-between">
                    <div class="flex justify-start">
                        <h1 class="text-[32px] text-[#1F384C] font-medium">
                            Status Surat
                        </h1>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 mt-6">
                    <div class="flex gap-2 items-center flex-wrap">
                        <div class="relative">
                            <x-form.select
                                name="jenis_surat"
                                id="jenis_surat"
                                :options="$jenisSurat"
                                placeholder="Jenis Surat"
                            />
                        </div>
                        <div class="relative">
                            <x-form.select
                                name="status_surat"
                                id="status_surat"
                                :options="$StatusSurat"
                                placeholder="Status Surat"
                            />
                        </div>
                        <button id="reset-filter-btn" type="button" class="h-10 flex items-center gap-1 px-4 py-2 bg-[#F0F2FF] border border-gray-200 text-base text-gray-700 font-semibold rounded-[10px] transition ml-2" title="Reset Filter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span class="hidden md:inline">Reset</span>
                        </button>
                    </div>
                    <div class="flex justify-end w-full md:w-auto mt-2 md:mt-0"> 
                        <input type="text" id="search_judul_surat" class="w-64 rounded-md text-black border caret-black bg-[#D9DCE2] border-gray-300 px-4 py-2" placeholder="Cari Judul Surat...">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <x-datatable-status 
                        id="tataUsahaStatusTable"
                        ajaxUrl="{{ route('tatausaha.statussurat.data') }}"
                        :columns="[
                            'nomor_surat' => 'Nomor',
                            'judul_surat' => 'Nama Surat',
                            'tanggal_pengajuan' => 'Tanggal',
                            'jenis_surat' => 'Jenis Surat',
                            'status' => 'Status',
                        ]"
                        userRole="Tata Usaha"
                    />
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        var table = $('#tataUsahaStatusTable').DataTable();
        // Custom search judul_surat
        $('#search_judul_surat').on('keyup', function() {
            table.ajax.reload();
        });
        // Handle filter changes
        $('#jenis_surat, #status_surat').on('change', function() {
            table.ajax.reload();
        });
        // Reset filter
        $('#reset-filter-btn').on('click', function() {
            $('#jenis_surat').val('').trigger('change');
            $('#status_surat').val('').trigger('change');
            $('#search_judul_surat').val('');
            table.ajax.reload();
        });
        // Kirim parameter search_judul_surat ke backend
        $.fn.dataTable.ext.errMode = 'throw';
        $('#tataUsahaStatusTable').on('preXhr.dt', function(e, settings, data) {
            data.search_judul_surat = $('#search_judul_surat').val();
        });
    });
</script>
@endpush
@endsection