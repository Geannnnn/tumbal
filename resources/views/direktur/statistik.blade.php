@extends('layouts.app')

@include('components.alertnotif')

@section('title','Statistik - Direktur')

@section('content')
<x-statistik 
    title="Statistik - Direktur"
    subtitle="Statistik surat yang telah diproses"
    :suratMenungguPersetujuan="$suratMenungguPersetujuan"
    :suratDisetujui="$suratDisetujui"
    :suratDitolak="$suratDitolak"
/>
@endsection 