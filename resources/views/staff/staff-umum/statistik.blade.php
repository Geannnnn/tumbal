@extends('layouts.app')

@section('title', 'statistik')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        
        <main class="flex-1 bg-white">
            @yield('content')

    <div class="bg-gradient-to-b from-white to-blue-50 h-full w-full px-9 py-8 flex-grow">
        <!-- Judul -->
        <h2 class="text-xl font-semibold mb-6">Rekapitulasi dan Statistik</h2>

        <!-- Filter -->
        <div class="flex flex-wrap gap-4 mb-6">
            <select class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700">
                <option disabled selected>Tahun</option>
                <option>2027</option>
                <option>2026</option>
                <option>2025</option>
                <option>2024</option>
                <option>2023</option>
            </select>
            
            <select class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700">
                <option disabled selected>Bulan</option>
                <option>Januari</option>
                <option>Februari</option>
                <option>Maret</option>
                <option>April</option>
                <option>Mei</option>
                <option>Juni</option>
                <option>Juli</option>
                <option>Agustus</option>
                <option>September</option>
                <option>Oktober</option>
                <option>November</option>
                <option>Desember</option>
            </select>
            <input type="date" class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700" />
            <span class="self-center">-</span>
            <input type="date" class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700" />
        </div>

        <!-- Surat -->
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="bg-[#F0F2FF] shadow-lg px-6 py-4 rounded-xl text-center flex-1">
                <div class="text-gray-500">Surat Diterima</div>
                <div class="text-2xl font-bold">4</div>
            </div>
            <div class="bg-[#F0F2FF] shadow-lg px-6 py-4 rounded-xl text-center flex-1">
                <div class="text-gray-500">Surat Diterbitkan</div>
                <div class="text-2xl font-bold">18</div>
            </div>
        </div>

        <!-- Grafik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

            <!-- Bar Chart -->
            <div class="bg-white shadow-lg rounded-2xl p-4">
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">Bar</span>
                </div>
                <canvas id="barChart" class="h-35"></canvas> <!-- mengubah ukuran visual-->
            </div>

            <!-- Pie Chart -->
            <div class="bg-white shadow-lg rounded-2xl p-3">
                <div class="flex justify-between mb-1">
                    <span class="font-semibold">Pie</span>
                </div>
                <canvas id="pieChart" class="h-130 w-130 mx-auto"></canvas> <!-- mengubah ukuran visual -->
            </div>  
        </div>

        <!-- Info Surat -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
            <!-- Surat Per Kategori -->
            <div class="rounded-2xl p-5 h-full">
                <h3 class="font-semibold mb-4 text-lg">Surat Per Kategori</h3>
                <ul class="space-y-2 divide-y divide-gray-200">
                    <li class="flex justify-between py-1">
                        <span>Surat Tugas</span>
                        <span>0 – Surat</span>
                    </li>
                    <li class="flex justify-between py-1">
                        <span>Surat Magang</span>
                        <span>0 – Surat</span>
                    </li>
                    <li class="flex justify-between py-1">
                        <span>Surat Lainnya</span>
                        <span>0 – Surat</span>
                    </li>
                </ul>
            </div>

            <!-- Status Surat -->
            <div class="rounded-2xl p-5 h-full">
                <h3 class="font-semibold mb-4 text-lg">Status Surat</h3>
                <ul class="space-y-2 divide-y divide-gray-200">
                    <li class="flex justify-between py-1">
                        <span>Ajukan</span>
                        <span>0 – Surat</span>
                    </li>
                    <li class="flex justify-between py-1">
                        <span>Diproses</span>
                        <span>0 – Surat</span>
                    </li>
                    <li class="flex justify-between py-1">
                        <span>Disetujui</span>
                        <span>0 – Surat</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    data: [20, 17, 32, 20, 14, 11, 15, 30, 25, 19, 12, 28],
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false // mematikan label di atas chart
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Pie Chart
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Diajukan', 'Diproses', 'Disetujui'],
            datasets: [{
                data: [4, 4, 39],
                backgroundColor: ['#6366f1', '#fbbf24', '#34d399']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
        }
    });
</script>
@endsection
