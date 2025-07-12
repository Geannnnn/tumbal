@props([
    'title' => 'Dashboard',
    'subtitle' => 'Selamat datang di dashboard',
    'suratDiterima' => 0,
    'suratDisetujui' => 0,
    'suratDitolak' => 0,
    'columns' => [],
    'ajaxUrl' => '',
    'showSearch' => true,
    'showEdit' => false,
    'showDelete' => false,
    'pageLength' => 5,
    'ordering' => true
])

<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-white p-12">
            @yield('content')
            <div class="title-page flex justify-between">
                <div class="flex justify-start">
                    <h1 class="text-[32px] text-[#1F384C] font-medium">
                        {{ $title }}
                    </h1>
                </div>
            </div>
        
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                <!-- Surat Diterima -->
                <div class="flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-b from-[#5A6ACF] to-blue-300 flex items-center justify-center mb-2 shadow">
                        <i class="fa-solid fa-file text-white text-4xl"></i>
                    </div>
                    <span class="text-[#5A6ACF] font-medium text-base mt-2">Surat Diterima</span>
                    <span class="text-blue-800 font-bold text-3xl mt-1">
                        {{ $suratDiterima }}
                    </span>
                </div>

                <!-- Surat Disetujui -->
                <div class="flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-b from-green-600 to-green-300 flex items-center justify-center mb-2 shadow">
                        <i class="fa-solid fa-file-circle-check text-white text-4xl"></i>
                    </div>
                    <span class="text-green-600 font-medium text-base mt-2">Surat Disetujui</span>
                    <span class="text-green-800 font-bold text-3xl mt-1">
                        {{ $suratDisetujui }}
                    </span>
                </div>
            
                <!-- Surat Ditolak -->
                <div class="flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-b from-red-600 to-red-300 flex items-center justify-center mb-2 shadow">
                        <i class="fa-solid fa-file-circle-xmark text-white text-4xl"></i>
                    </div>
                    <span class="text-red-600 font-medium text-base mt-2">Surat Ditolak</span>
                    <span class="text-red-800 font-bold text-3xl mt-1">
                        {{ $suratDitolak }}
                    </span>
                </div>
            </div>
            
            <div class="flex pt-10">
                <div class="flex justify-between w-full">
                    <div class="flex justify-start">
                        <h1 class="font-semibold text-[22px]">Surat Menunggu Persetujuan</h1>
                    </div>
                    @if($showSearch)
                    <div class="flex justify-end mt-4">
                        <input type="search" name="" id="custom-search" class="text-black rounded-[10px] bg-[#D9DCE2] caret-black py-2 px-4" placeholder="Cari Surat...">
                    </div>
                    @endif
                </div>
            </div>
            <div class="w-full">
                <x-datatable
                    id="surat-table"
                    :columns="$columns"
                    :ajaxUrl="$ajaxUrl"
                    :ordering="$ordering"
                    :lengthMenu="false"
                    :pageLength="$pageLength"
                    :showEdit="$showEdit"
                    :showDelete="$showDelete"
                    :search="$showSearch"
                />
            </div>
        </main>
    </div>
</div> 