@extends('layouts.app')

@include('components.alertnotif')

@section('title','Persetujuan Surat - Direktur')

@section('content')
<x-persetujuan-surat 
    title="Persetujuan Surat - Direktur"
    subtitle="Kelola surat yang menunggu persetujuan"
    :columns="[
        'no' => 'No',
        'nomor_surat' => 'Nomor Surat',
        'judul_surat' => 'Nama Surat',
        'tanggal_pengajuan' => 'Tanggal Pengajuan',
        'jenis_surat' => 'Jenis Surat',
        'pengusul' => 'Pengusul',
        'lampiran' => 'Lampiran',
        'status' => 'Status'
    ]" 
    :ajaxUrl="route('direktur.persetujuansurat.data')"
    :showSearch="true"
    :showEdit="false"
    :showDelete="false"
    :pageLength="10"
    :ordering="false"
/>
@endsection 