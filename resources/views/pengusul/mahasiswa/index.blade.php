@extends('layouts.app')


@section('title','Dashboard Mahasiswa')

@section('content')

<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-4 sm:p-6 md:p-8 lg:p-12">
            @yield('content')
            <div class="title-page flex flex-col md:flex-row justify-between gap-4">
                <h1 class="text-2xl md:text-[32px] text-[#1F384C] font-medium">
                    Dashboard
                </h1>
                
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    <div class="flex bg-[#707FDD] rounded-[15px] font-medium h-10 overflow-hidden">
                        <button class="py-2 px-4 hover:cursor-pointer rounded-[15px] " id="btn-jarak">Jarak</button>
                        <button class="py-2 px-4 hover:cursor-pointer rounded-[15px] " id="btn-bulan">Bulan</button>
                        <button class="inline-block bg-[#4628A4] text-white rounded-[15px] py-2 px-4 hover:cursor-pointer" id="btn-tahun">Tahun</button>
                    </div>
                    <div class="" id="input-container">
                        <select name="year" id="year" class="bg-[#707FDD] py-2 px-4 rounded-[5px]"></select>
                    </div>
                </div>
            </div>
        

            <div class="flex flex-col sm:flex-row lg:gap-1 gap-6 mt-6">
                <!-- Surat Diterima -->
                <div class="flex-1 flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6 mx-2">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-b from-[#5A6ACF] to-blue-300 flex items-center justify-center mb-2 shadow">
                        <i class="fa-solid fa-file text-white text-4xl"></i>
                    </div>
                    <span class="text-[#5A6ACF] font-medium text-base mt-2">Surat Diterima</span>
                    <span class="text-blue-800 font-bold text-3xl mt-1">{{ $suratDiterima }}</span>
                </div>
                <!-- Surat Ditolak -->
                <div class="flex-1 flex flex-col items-center bg-[#F1F2F7] shadow-md shadow-blue-100 rounded-2xl py-6 mx-2">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-b from-[#5A6ACF] to-blue-300 flex items-center justify-center mb-2 shadow">
                        <i class="fa-solid fa-file text-white text-4xl"></i>
                    </div>
                    <span class="text-blue-500 font-medium text-base mt-2">Surat Ditolak</span>
                    <span class="text-blue-800 font-bold text-3xl mt-1">{{ $suratDitolak }}</span>
                </div>
            </div>
            
            <div class="flex pt-10">
                <div class="flex justify-between w-full">
                    <div class="flex justify-start">
                        <h1 class="font-semibold text-[22px]">Riwayat Dokumen</h1>
                    </div>
                    <div class="flex justify-end mt-4">
                        <input type="search" name="" id="custom-search" class="text-black rounded-[10px] bg-[#D9DCE2] caret-black py-2 px-4" placeholder="Cari Surat...">
                    </div>
                </div>
            </div>
            <div class="w-full">
                <x-datatable
                    id="surat-table"
                    :columns="$columns"
                    ajaxUrl="{{ route('mahasiswa.search') }}"
                    :ordering="false"
                    :lengthMenu="false"
                    :pageLength="5"
                    :showEdit="false"
                    :showDelete="false"
                    :search="true"
                />

                
            </div>
        </main>
    </div>
</div>
@push('scripts')
    
<script>
                        
    const btnJarak = document.getElementById('btn-jarak');
    const btnBulan = document.getElementById('btn-bulan');
    const btnTahun = document.getElementById('btn-tahun');
    const inputContainer = document.getElementById('input-container');

   
    function setActiveButton(button) {
        btnJarak.classList.remove('bg-[#4628A4]', 'text-white');
        btnBulan.classList.remove('bg-[#4628A4]', 'text-white');
        btnTahun.classList.remove('bg-[#4628A4]', 'text-white');
        button.classList.add('bg-[#4628A4]', 'text-white');
    }

    
    function updateInputField(buttonId) {
        inputContainer.innerHTML = ""; 

        if (buttonId === 'btn-jarak') {
            
            inputContainer.innerHTML = `
                <input type="date" name="start-date" id="start-date" class="bg-[#707FDD] py-2 px-4 rounded-[5px]" />
                <input type="date" name="end-date" id="end-date" class="bg-[#707FDD] py-2 px-4 rounded-[5px]" />
            `;
        } else if (buttonId === 'btn-bulan') {
            const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
            const monthSelect = document.createElement("select");
            monthSelect.name = "month";
            monthSelect.id = "month";
            monthSelect.classList.add("bg-[#707FDD]", "py-2", "px-4", "rounded-[5px]");
            months.forEach((month, index) => {
                const option = document.createElement("option");
                option.value = index + 1;
                option.textContent = month;
                monthSelect.appendChild(option);
            });
            inputContainer.appendChild(monthSelect);
        } else if (buttonId === 'btn-tahun') {
            const startYear = 2000;
            const endYear = new Date().getFullYear();

            const yearSelect = document.createElement("select");
            yearSelect.name = "year";
            yearSelect.id = "year";
            yearSelect.classList.add("bg-[#707FDD]", "py-2", "px-4", "rounded-[5px]");

            for (let year = endYear; year >= startYear; year--) {
                const option = document.createElement("option");
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }
            inputContainer.appendChild(yearSelect);
        }
    }

    setActiveButton(btnTahun);
    updateInputField('btn-tahun');

    btnJarak.addEventListener('click', function() {
        setActiveButton(btnJarak);
        updateInputField('btn-jarak');
    });

    btnBulan.addEventListener('click', function() {
        setActiveButton(btnBulan);
        updateInputField('btn-bulan');
    });

    btnTahun.addEventListener('click', function() {
        setActiveButton(btnTahun);
        updateInputField('btn-tahun');
    });
</script>

@endpush

@endsection