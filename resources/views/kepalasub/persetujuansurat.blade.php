@extends('layouts.app')

@include('components.alertnotif')

@section('title','Persetujuan Surat - Kepala Sub')

@section('content')
<x-alertnotif />
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-4 sm:p-6 md:p-8 lg:p-12">
            <div class="container mx-auto">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Persetujuan Surat</h1>
                    <p class="text-gray-600">Kelola surat yang diajukan untuk persetujuan</p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-4">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">Daftar Surat Diajukan</h2>
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
                            'judul_surat' => 'Judul Surat',
                            'jenis_surat' => 'Jenis Surat', 
                            'pengusul' => 'Pengusul',
                            'tanggal_pengajuan' => 'Tanggal Pengajuan',
                            'status' => 'Status',
                            'actions' => 'Aksi'
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