@extends('layouts.app')

@include('components.alertnotif')

@section('title','Kelola Jenis Surat')

@section('content')
<x-alertnotif />
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')
             <div class="title-page flex justify-between">
                <div class="flex justify-start">
                    <h1 class="text-[32px] text-[#1F384C] font-medium">
                        Kelola Jenis Surat
                    </h1>
                </div>
             </div>
            <x-dtable 
                id="jenis-surat-table"
                :columns="['jenis_surat' => 'Jenis Surat', 'aksi' => 'Aksi']"
                :data="$jsdata->map(function($item) {
                    return [
                        'jenis_surat' => $item->jenis_surat,
                        'aksi' => '
                            <form action=\''.route('admin.jenissurat.destroy', $item->id_jenis_surat).'\' method=\'POST\' style=\'display:inline\'>
                                <input type=\'hidden\' name=\'_token\' value=\''.csrf_token().'\'>
                                <input type=\'hidden\' name=\'_method\' value=\'DELETE\'>
                                <button type=\'submit\' class=\'text-red-600\'>Hapus</button>
                            </form>
                            <form action=\''.route('admin.jenissurat.update', $item->id_jenis_surat).'\' method=\'POST\' style=\'display:inline\'>
                                <input type=\'hidden\' name=\'_token\' value=\''.csrf_token().'\'>
                                <input type=\'hidden\' name=\'_method\' value=\'PUT\'>
                                <input type=\'text\' name=\'jenis_surat\' value=\''.$item->jenis_surat.'\' class=\'border p-1 rounded w-32\'>
                                <button type=\'submit\' class=\'text-green-600\'>Edit</button>
                            </form>'
                    ];
                })"
            />

        </main>
    </div>
</div>