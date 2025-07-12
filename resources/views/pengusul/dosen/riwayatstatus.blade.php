@extends('layouts.app')
@section('title','Status Surat')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-12">
            <x-status-riwayat :riwayat="$riwayat" :showKomentar="true" :routeBack="route('dosen.statussurat')" :judulSurat="$judulSurat" :jenisSurat="$jenisSurat" />
        </main>
    </div>
</div>
@endsection