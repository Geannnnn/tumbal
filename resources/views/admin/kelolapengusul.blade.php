@extends('layouts.app')

@include('components.alertnotif')

@section('title','Kelola Pengusul')

@section('content')
<x-alertnotif />
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')
        </main>
    </div>
</div>