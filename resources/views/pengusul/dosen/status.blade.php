@extends('layouts.app')

@section('title','Status Surat')

@section('content')

<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')

            <div class="flex gap-4">
                <x-form.select
                    name="jenis_surat"
                    id="jenis_surat"
                    :options="[
                        'Surat Tugas' => 'Surat Tugas',
                        'Surat Magang' => 'Surat Magang',
                        'Surat Default' => 'Surat *',
                    ]"
                    placeholder="Jenis Surat"

                />
                <x-form.select
                    name="status_surat"
                    id="status_surat"
                    :options="[
                        'Diproses' => 'Diproses',
                        'Menunggu' => 'Menunggu',
                        'Disetujui' => 'Disetujui',
                        'Ditolak' => 'Ditolak',
                    ]"
                    placeholder="Status Surat"

                />

                <x-yearselect name="year" id="year" :start="2000" />
                    {{-- <div class="relative flex justify-center  items-center ">
                        <i class="fa-solid fa-calendar-days absolute left-3 top-1/2 transform -translate-y-1/2 text-black pointer-events-none"></i>
                        <select id="year" name="year"
                        class="bg-[#F0F2FF] text-black appearance-none py-2 pl-10 pr-4 rounded-[5px]">
                        </select>
                    </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const select = document.getElementById('year');
                        const startYear = 2000;
                        const currentYear = new Date().getFullYear();
                
                        for (let year = currentYear; year >= startYear; year--) {
                            const option = document.createElement('option');
                            option.value = year;
                            option.text = year;
                
                            if (year === currentYear) {
                                option.selected = true;
                            }
                
                            select.appendChild(option);
                        }
                    });
                </script> --}}

            </div>

            <div class="flex p-10">
                <x-backplat 
                :title="'Status Surat'"
                :subtitle="false">
                
                <x-datatable
                    :search="false"
                    :showLengthMenu="false"
                    :columns="['Nomor Surat','Nama Surat','Tanggal','Jenis Surat','Status','Aksi']"
                    :data="[
                    ['Row 1 Data 2','Row 1 Data 3','Row 1 Data 4','Row 1 Data 5','Row 1 Data 6',''],
                    ['Row 2 Data 2','Row 2 Data 3','Row 2 Data 4','Row 2 Data 5','Row 2 Data 6',''],
                    ]"
                />

                </x-backplat>
            </div>

            

        </main>
    </div>
</div>

@endsection