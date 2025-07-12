@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<x-dashboard-layout 
    title="Dashboard"
    :diterima="$suratDiterima"
    :ditolak="$suratDitolak"
    searchPlaceholder="Cari Surat..."
    ajaxRoute="{{ route('mahasiswa.search') }}"
    :columns="$columns"
/>
@endsection