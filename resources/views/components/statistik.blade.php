@props([
    'title' => 'Statistik',
    'subtitle' => 'Statistik surat yang telah diproses',
    'suratMenungguPersetujuan' => 0,
    'suratDisetujui' => 0,
    'suratDitolak' => 0
])

@extends('layouts.app')

@include('components.alertnotif')

@section('title', $title)

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Header Section -->
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">{{ $title }}</h1>
                    <p class="text-gray-600 mt-1">{{ $subtitle }}</p>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-blue-500 text-white rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-600 bg-opacity-75">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-100">Menunggu Persetujuan</p>
                                <p class="text-2xl font-semibold">{{ $suratMenungguPersetujuan }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-500 text-white rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-600 bg-opacity-75">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-100">Disetujui</p>
                                <p class="text-2xl font-semibold">{{ $suratDisetujui }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-500 text-white rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-600 bg-opacity-75">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-red-100">Ditolak</p>
                                <p class="text-2xl font-semibold">{{ $suratDitolak }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold mb-4">Grafik Statistik</h2>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="statistikChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statistikChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Menunggu Persetujuan', 'Disetujui', 'Ditolak'],
            datasets: [{
                data: [
                    {{ $suratMenungguPersetujuan }},
                    {{ $suratDisetujui }},
                    {{ $suratDitolak }}
                ],
                backgroundColor: [
                    '#3B82F6', // Blue
                    '#10B981', // Green
                    '#EF4444'  // Red
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
});
</script>
@endsection 