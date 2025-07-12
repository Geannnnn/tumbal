@extends('layouts.app')

@section('title', 'Ajukan Ulang Surat')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-10">
            <x-form.edit-surat 
                :action="$action"
                :routeDraft="$routeDraft"
                :surat="$surat"
                :jenisSurat="$jenisSurat"
                :ketua="$ketua"
                :anggota="$anggota"
                :namaPengaju="$namaPengaju"
            />
        </main>
    </div>
</div>
@endsection