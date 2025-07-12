@extends('layouts.app')

@include('components.alertnotif')

@section('title','Persetujuan Surat - Kepala Sub')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-white p-4 sm:p-6 md:p-8 lg:p-12">
            <div class="container mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Persetujuan Surat</h1>
                    <p class="text-gray-600">Kelola surat yang menunggu persetujuan</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-4">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Daftar Surat Menunggu Persetujuan</h2>
                            <div class="relative">
                                <input type="text" id="custom-search" placeholder="Cari surat..." 
                                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <x-datatable 
                        id="surat-diajukan-table"
                        :columns="[
                            ['data' => 'DT_RowIndex', 'name' => 'DT_RowIndex', 'title' => 'No'],
                            ['data' => 'judul_surat', 'name' => 'judul_surat', 'title' => 'Judul Surat'],
                            ['data' => 'jenis_surat', 'name' => 'jenis_surat', 'title' => 'Jenis Surat'], 
                            ['data' => 'pengusul', 'name' => 'pengusul', 'title' => 'Pengusul'],
                            ['data' => 'tanggal_pengajuan', 'name' => 'tanggal_pengajuan', 'title' => 'Tanggal Pengajuan'],
                            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
                            ['data' => 'actions', 'name' => 'actions', 'title' => 'Aksi']
                        ]"
                        ajax-url="{{ route('kepalasub.persetujuansurat.data') }}"
                        :search="true"
                        :ordering="false"
                        :paging="true"
                        :info="true"
                        :length-menu="false"
                        :page-length="5"
                        :show-edit="false"
                        :show-delete="false"
                    />
                </div>
            </div>
        </main>
    </div>
</div>
@endsection