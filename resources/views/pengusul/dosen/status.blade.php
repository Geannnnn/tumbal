@extends('layouts.app')

@section('title','Status Surat')

@section('content')
<x-status-index 
    userRole="Dosen"
    subtitle=""
    :jenisSurat="$jenisSurat"
    :statusSurat="$StatusSurat"
    :ajaxRoute="route('dosen.statussurat.data')"
    searchPlaceholder="Cari status surat..."
/>
@endsection