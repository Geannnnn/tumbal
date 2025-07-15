@extends('layouts.app')

@section('title','Status Surat')

@section('content')
<x-status-index 
    userRole="Mahasiswa"
    subtitle="Riwayat dan tahapan status surat yang diajukan."
    :jenisSurat="$jenisSurat"
    :statusSurat="$StatusSurat"
    :ajaxRoute="route('mahasiswa.statussurat.data')"
    searchPlaceholder="Cari nama surat..."
/>
@endsection