@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
<x-dashboard-layout 
    title="Dashboard"
    :diterima="$suratDiterima"
    :ditolak="$suratDitolak"
    searchPlaceholder="Cari Surat..."
    ajaxRoute="{{ route('dosen.search') }}"
    :columns="$columns"
/>
@endsection