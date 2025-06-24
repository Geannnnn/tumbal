@extends('layouts.app')

@include('components.alertnotif')

@section('title','Status Surat - Staff Umum')

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')

            <div class="flex gap-4 mb-10">
                <x-form.select
                    name="jenis_surat"
                    id="jenis_surat"
                    :options="$jenisSurat"
                    placeholder="Jenis Surat"
                />
                <x-form.select
                    name="status_surat"
                    id="status_surat"
                    :options="$StatusSurat"
                    placeholder="Status Surat"
                />
                <x-yearselect name="year" id="year" :start="2000" />
            </div>

            <x-backplat 
                :title="'Status Surat'"
                :subtitle="'Status Surat Tugas dan Surat Undangan Kegiatan dari Dosen'"
                :searchPlaceholder="'Cari status surat...'"
                :search="true">
                
                <x-datatable-status 
                    id="staffUmumStatusTable"
                    ajaxUrl="{{ route('staffumum.statussurat.data') }}"
                    :columns="[
                        'nomor_surat' => 'Nomor',
                        'judul_surat' => 'Nama Surat',
                        'tanggal_pengajuan' => 'Tanggal',
                        'jenis_surat' => 'Jenis Surat',
                        'status' => 'Status',
                    ]"
                    userRole="Staff Umum"
                />

            </x-backplat>

        </main>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle filter changes
        $('#jenis_surat, #status_surat, #year').on('change', function() {
            $('#staffUmumStatusTable').DataTable().ajax.reload();
        });
    });
</script>
@endpush

@endsection