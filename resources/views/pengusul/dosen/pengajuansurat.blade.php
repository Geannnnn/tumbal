@extends('layouts.app')

@section('title','Pengajuan Surat')

@section('content')
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        
        <main class="flex-1 bg-white p-10">
            @yield('content')

            <div class="" id="ajukanData">
                <div class="pb-7.5 pl-7.5">
                    <button id="ajukanDataHide" class="bg-[#EDE9EC] text-[#907F7F] font-medium py-2 px-9 rounded-[20px] hover:cursor-pointer hover:scale-110 transition-transform duration-300 transform ">
                        Ajukan
                    </button>
                </div>
        
                {{-- Background colored --}}
                <div class="w-full h-full max-h-180 bg-[#F0F2FF] rounded-[70px]">
                    
                    <div class="pl-15 pt-8">
                        <h1 class="font-medium text-[28px]">Pengajuan Surat</h1>
                        <div class="flex justify-between w-full">
                            <h1 class="text-[#6D727C] font-medium text-[24px] py-4">List Pengajuan Surat Politeknik Negeri Batam</h1>
        
                            <div class="flex justify-end py-4 pr-5">
                                <x-form.search name="" id="custom-search" placeholder="Search..." bgColor="#D9DCE2" textColor="black" class="w-full" />
                            </div>
                        </div>
                    </div>
                    <hr class="border-[#DEDBDB]">
        
                    {{-- Data Table --}}
        
                    <div id="datatable-container" class="p-5">
                        <x-datatable
                            id="pengajuan-table"
                            :columns="$columns"
                            ajaxUrl="{{ route('pengajuan.search') }}"
                            :ordering="true"
                            :lengthMenu="false"
                            :pageLength="5"
                            :showEdit="false"
                            :showDelete="false"
                        />
                    </div>  
        
                </div>
            </div>

            <x-pengajuan-surat :jenisSurat="$jenisSurat" />

        

            @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ajukanDataHide = document.getElementById('ajukanDataHide');
                    const ajukanDataShow = document.getElementById('ajukanData');
                    const ajukanFormShow = document.getElementById('ajukanForm');
                    const ajukanFormHide = document.getElementById('ajukanFormHide');
            
                    if (ajukanDataHide && ajukanDataShow && ajukanFormShow && ajukanFormHide) {
                        ajukanDataHide.addEventListener('click', () => {
                            ajukanDataShow.classList.add('hidden');
                            ajukanFormShow.classList.remove('hidden');
                        });
            
                        ajukanFormHide.addEventListener('click', () => {
                            ajukanFormShow.classList.add('hidden');
                            ajukanDataShow.classList.remove('hidden');
                        });
                    }
                });
            </script>
            @endpush
        
        </main>
</div>
</div>
@endsection