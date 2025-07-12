@extends('layouts.app')

@section('title','Pengajuan Surat')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-10">
            @yield('content')

            <div class="" id="ajukanData">
                <div class="pb-7.5 pl-7.5">
                    <button id="ajukanDataHide" class="bg-[#EDE9EC] text-[#907F7F] font-medium py-2 px-9 rounded-[20px] hover:cursor-pointer hover:scale-110 transition-transform duration-300 transform ">
                        Ajukan
                    </button>
                </div>
        
                {{-- Background colored --}}
                <div class="bg-white rounded-lg shadow-md p-6">
                    
                    <div class="pl-15 pt-8">
                        <h1 class="font-medium sm:text-[10px] md:text-[10px] lg:text-[25px]">Pengajuan Surat</h1>
                        <div class="flex justify-between w-full">
                            <h1 class="text-[#6D727C] font-medium text-[18px] py-4">List Pengajuan Surat Politeknik Negeri Batam</h1>
        
                            <div class="flex justify-end py-4 pr-5">
                                <x-form.search name="" id="custom-search" placeholder="Cari Surat..." bgColor="#D9DCE2" textColor="black" class="w-full" />
                            </div>
                        </div>
                    </div>
                    <hr class="border-[#DEDBDB]">
        
                    {{-- Data Table --}}
        
                    <div id="datatable-container" class="p-5">
                        <x-datatable
                            id="pengajuan-table"
                            :columns="[
                                'no' => 'No',
                                'judul_surat' => 'Judul Surat',
                                'tanggal_pengajuan' => 'Tanggal Pengajuan',
                                'jenis_surat' => 'Jenis Surat',
                                'dibuat_oleh' => 'Dibuat Oleh',
                                'ketua' => 'Ketua',
                                'anggota' => 'Anggota',
                                'lampiran' => 'Lampiran',
                                'deskripsi' => 'Deskripsi',
                            ]"
                            ajaxUrl="{{ route('pengajuan.search') }}"
                            :ordering="false"
                            :lengthMenu="false"
                            :pageLength="5"
                            :showEdit="false"
                            :showDelete="false"
                            :search="true"
                            
                        />
                    </div>      
                </div>
            </div>

            <x-pengajuan-surat :jenisSurat="$jenisSurat" :namaPengaju="$namaPengaju" />

        

        <script>
            const ajukanDataHide = document.getElementById('ajukanDataHide');
            const ajukanDataShow = document.getElementById('ajukanData');
            const ajukanFormShow = document.getElementById('ajukanForm');
            const ajukanFormHide = document.getElementById('ajukanFormHide');

            ajukanDataHide.addEventListener('click',function(){
                ajukanDataShow.classList.add('hidden');
                ajukanFormShow.classList.remove('hidden');
            });

            ajukanFormHide.addEventListener('click',function(){
                ajukanFormShow.classList.add('hidden');
                ajukanDataShow.classList.remove('hidden');
            })

        </script>
        
        </main>
</div>
</div>
@endsection