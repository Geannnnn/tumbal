@extends('layouts.app')

@section('title', 'Kelola Pengusul')

@section('content')
<x-alertnotif />
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')

        <main class="flex-1 bg-white p-12">
            <x-backplat title="Kelola Pengusul" subtitle="" :search="true">
                <x-datatable
                    id="pengusul-table"
                    ajax-url="{{ route('admin.pengusul.data') }}"
                    :columns="[
                        'nama' => 'Nama',
                        'email' => 'Email',
                        'nim' => 'NIM',
                        'nip' => 'NIP',
                        'role' => 'Role',
                    ]"
                    :show-edit="true"
                    :show-delete="true"
                    :ordering="false"
                    :lengthMenu="false"
                    :pageLength="5"
                    :search="true"
                />
            </x-backplat>
        </main>
    </div>
</div>
@endsection