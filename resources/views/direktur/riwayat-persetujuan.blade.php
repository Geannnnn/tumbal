@extends('layouts.app')

@include('components.alertnotif')

@section('title','Riwayat Persetujuan - Direktur')

@section('content')
<x-riwayat-persetujuan 
    title="Riwayat Persetujuan - Direktur"
    subtitle="Riwayat surat yang telah disetujui atau ditolak"
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
    :ajaxUrl="route('direktur.riwayat-persetujuan.data')"
    :showSearch="true"
    :showEdit="false"
    :showDelete="false"
    :pageLength="10"
    :ordering="false"
/>
@endsection 