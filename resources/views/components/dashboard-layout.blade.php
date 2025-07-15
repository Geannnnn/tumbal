<!-- resources/views/components/dashboard-layout.blade.php -->
@props([
    'title' => 'Dashboard',
    'diterima' => 0,
    'ditolak' => 0,
    'searchPlaceholder' => 'Cari Surat...',
    'ajaxRoute' => '',
    'columns' => [],
])

<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')
    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')
        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="title-page flex justify-between">
                    <div class="flex justify-start">
                        <h1 class="text-[32px] text-[#1F384C] font-medium">
                            {{ $title }}
                        </h1>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row lg:gap-1 gap-6 mt-6">
                    <div class="flex-1 flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6 mx-2">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-b from-[#5A6ACF] to-blue-300 flex items-center justify-center mb-2 shadow">
                            <i class="fa-solid fa-file text-white text-4xl"></i>
                        </div>
                        <span class="text-[#5A6ACF] font-medium text-base mt-2">Surat Diterima</span>
                        <span class="text-blue-800 font-bold text-3xl mt-1">{{ $diterima }}</span>
                    </div>
                    <div class="flex-1 flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6 mx-2">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-b from-[#5A6ACF] to-blue-300 flex items-center justify-center mb-2 shadow">
                            <i class="fa-solid fa-file text-white text-4xl"></i>
                        </div>
                        <span class="text-blue-500 font-medium text-base mt-2">Surat Ditolak</span>
                        <span class="text-blue-800 font-bold text-3xl mt-1">{{ $ditolak }}</span>
                    </div>
                </div>
                <div class="flex pt-10">
                    <div class="flex justify-between w-full">
                        <div class="flex justify-start">
                            <h1 class="font-semibold text-[22px]">Riwayat Dokumen</h1>
                        </div>
                        <div class="flex justify-end mt-4">
                            <input type="search" name="" id="custom-search" placeholder="{{ $searchPlaceholder }}" class="text-black rounded-[10px] bg-[#D9DCE2] caret-black py-2 px-4">
                        </div>
                    </div>
                </div>
                <div class="w-full">
                    <x-datatable
                        id="surat-table"
                        :columns="array_merge($columns, ['aksi' => 'Aksi'])"
                        :ajaxUrl="$ajaxRoute"
                        :ordering="false"
                        :lengthMenu="false"
                        :pageLength="5"
                        :showEdit="false"
                        :showDelete="false"
                        :search="true"
                    />
                </div>
            </div>
        </main>
    </div>
</div>
