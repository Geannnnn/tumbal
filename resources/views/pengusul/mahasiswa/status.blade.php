@extends('layouts.app')

@section('title','Status Surat')

@section('content')

<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')

            <div class="flex gap-4">
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

            <div class="flex p-10">
                <x-backplat 
                :title="'Status Surat'"
                :subtitle="false">
                
                    <x-datatable-status 
                        id="mahasiswaStatusTable"
                        ajaxUrl="{{ route('statusSurat.data') }}"
                        :columns="[
                            'nomor_surat' => 'Nomor',
                            'judul_surat' => 'Nama Surat',
                            'tanggal_pengajuan' => 'Tanggal',
                            'jenis_surat' => 'Jenis Surat',
                            'status' => 'Status',
                        ]"
                    />

                </x-backplat>
            </div>

            

        </main>
    </div>
</div>

@endsection