@extends('layouts.app')

@include('components.alertnotif')

@section('title','Dashboard Kepala Sub')

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-4 sm:p-6 md:p-8 lg:p-12">
            @yield('content')
        </main>
    </div>
</div>
@endsection