@extends('layouts.app')

@include('components.alertnotif')

@section('title','Dashboard - Direktur')

@section('content')
<x-dashboard 
    title="Dashboard Direktur"
    :suratDiterima="$suratDiterima"
    :suratDisetujui="$suratDisetujui"
    :suratDitolak="$suratDitolak"
    :columns="$columns"
    :ajaxUrl="route('direktur.dashboard.data')"
    :showSearch="true"
    :showEdit="false"
    :showDelete="false"
    :pageLength="5"
    :ordering="true"
/>
@endsection 