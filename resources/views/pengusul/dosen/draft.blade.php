@extends('layouts.app')

@section('title','Draft')

@section('content')

<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')

            <x-backplat
                :title="'Draft'" 
                :subtitle="'Draft Surat Politeknik Negeri Batam'" 
                :search="true">
                <a href=""></a>
                <x-datatable
                    :search="false"
                    :columns="['Judul Surat']"
                    :data="[
                        ['Row 1 Data 1'],
                        ['Row 2 Data 1'],

                    ]"
                    :showLengthMenu="false"
                    :showEdit="true"
                    :showDelete="true"
                />

            </x-backplat>

           

        </main>
    </div>
</div>

@endsection
