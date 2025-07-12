@extends('layouts.app')

@section('title', 'Statistik - Staff Umum')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        
        <main class="flex-1 bg-white">
            @yield('content')

    <div class="bg-gradient-to-b from-white to-blue-50 h-full w-full px-9 py-8 flex-grow">
        <!-- Judul -->
        <h2 class="text-xl font-semibold mb-6">Rekapitulasi dan Statistik</h2>

        <!-- Filter -->
        <form method="GET" action="{{ route('staffumum.statistik') }}" class="flex flex-wrap gap-4 mb-6">
            <select name="year" class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700">
                <option value="">Pilih Tahun</option>
                @for($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            
            <select name="month" class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700">
                <option value="">Pilih Bulan</option>
                <option value="1" {{ $month == 1 ? 'selected' : '' }}>Januari</option>
                <option value="2" {{ $month == 2 ? 'selected' : '' }}>Februari</option>
                <option value="3" {{ $month == 3 ? 'selected' : '' }}>Maret</option>
                <option value="4" {{ $month == 4 ? 'selected' : '' }}>April</option>
                <option value="5" {{ $month == 5 ? 'selected' : '' }}>Mei</option>
                <option value="6" {{ $month == 6 ? 'selected' : '' }}>Juni</option>
                <option value="7" {{ $month == 7 ? 'selected' : '' }}>Juli</option>
                <option value="8" {{ $month == 8 ? 'selected' : '' }}>Agustus</option>
                <option value="9" {{ $month == 9 ? 'selected' : '' }}>September</option>
                <option value="10" {{ $month == 10 ? 'selected' : '' }}>Oktober</option>
                <option value="11" {{ $month == 11 ? 'selected' : '' }}>November</option>
                <option value="12" {{ $month == 12 ? 'selected' : '' }}>Desember</option>
            </select>
            
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700" placeholder="Tanggal Mulai" />
            <span class="self-center">-</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2 rounded-md bg-[#F0F2FF] text-gray-700" placeholder="Tanggal Akhir" />
            
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                Filter
            </button>
            
            <a href="{{ route('staffumum.statistik') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                Reset
            </a>
        </form>

        <!-- Surat -->
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="bg-[#F0F2FF] shadow-lg px-6 py-4 rounded-xl text-center flex-1">
                <div class="text-gray-500">Surat Diterima</div>
                <div class="text-2xl font-bold">{{ $suratDiterima }}</div>
            </div>
            <div class="bg-[#F0F2FF] shadow-lg px-6 py-4 rounded-xl text-center flex-1">
                <div class="text-gray-500">Surat Diterbitkan</div>
                <div class="text-2xl font-bold">{{ $suratDiterbitkan }}</div>
            </div>
            <div class="bg-[#F0F2FF] shadow-lg px-6 py-4 rounded-xl text-center flex-1">
                <div class="text-gray-500">Surat Ditolak</div>
                <div class="text-2xl font-bold">{{ $suratDitolak }}</div>
            </div>
        </div>

        <!-- Grafik -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">

            <!-- Bar Chart -->
            <div class="bg-white shadow-lg rounded-2xl p-4">
                <div class="flex justify-between mb-2">
                    <span class="font-semibold">Statistik Bulanan</span>
                </div>
                <canvas id="barChart" class="h-35"></canvas>
            </div>

            <!-- Pie Chart -->
            <div class="bg-white shadow-lg rounded-2xl p-3">
                <div class="flex justify-between mb-1">
                    <span class="font-semibold">Status Surat</span>
                </div>
                <canvas id="pieChart" class="h-130 w-130 mx-auto"></canvas>
            </div>  
        </div>

        <!-- Info Surat -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
            <!-- Surat Per Kategori -->
            <div class="rounded-2xl p-5 h-full">
                <h3 class="font-semibold mb-4 text-lg">Surat Per Kategori</h3>
                <ul class="space-y-2 divide-y divide-gray-200">
                    @forelse($suratPerKategori as $kategori)
                    <li class="flex justify-between py-1">
                            <span>{{ $kategori['nama'] }}</span>
                            <span>{{ $kategori['count'] }} – Surat</span>
                    </li>
                    @empty
                    <li class="flex justify-between py-1">
                            <span>Tidak ada data</span>
                        <span>0 – Surat</span>
                    </li>
                    @endforelse
                </ul>
            </div>

            <!-- Status Surat -->
            <div class="rounded-2xl p-5 h-full">
                <h3 class="font-semibold mb-4 text-lg">Status Surat</h3>
                <ul class="space-y-2 divide-y divide-gray-200">
                    @forelse($statusSurat as $status)
                    <li class="flex justify-between py-1">
                            <span>{{ $status['nama'] }}</span>
                            <span>{{ $status['count'] }} – Surat</span>
                    </li>
                    @empty
                    <li class="flex justify-between py-1">
                            <span>Tidak ada data</span>
                        <span>0 – Surat</span>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Bar Chart
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    data: @json($monthlyData),
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
                    display: false
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
            labels: @json(array_keys($pieChartData)),
            datasets: [{
                data: @json(array_values($pieChartData)),
                backgroundColor: ['#6366f1', '#fbbf24', '#34d399', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
        }
    });
</script>
@endsection
