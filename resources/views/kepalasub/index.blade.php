@extends('layouts.app')

@include('components.alertnotif')

@section('title','Dashboard Kepala Sub')

@section('content')
<x-dashboard 
    title="Dashboard Kepala Sub"
    :suratDiterima="$suratDiterima"
    :suratDisetujui="$suratDisetujui"
    :suratDitolak="$suratDitolak"
    :columns="$columns"
    :ajaxUrl="route('kepalasub.dashboard.data')"
    :showSearch="true"
    :showEdit="false"
    :showDelete="false"
    :pageLength="5"
    :ordering="true"
/>
@endsection