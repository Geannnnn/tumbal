@extends('layouts.app')

@section('title','Riwayat Status Surat - Staff Umum')

@section('content')

<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-12">
            <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow p-8">
                <h2 class="text-2xl font-bold mb-6">Riwayat status surat</h2>
                <hr class="mb-6">
                <div class="relative">
                    <ol class="ml-6">
                        @php
                            \Carbon\Carbon::setLocale('id');
                            $count = count($riwayat);
                        @endphp
                        @forelse($riwayat as $i => $item)
                            <li class="relative flex min-h-[64px]">
                                <!-- Garis atas (jika bukan item pertama) -->
                                @if($i > 0)
                                    <span class="absolute left-0 top-0 w-1 h-1/2 {{ str_replace('bg-', 'bg-', $riwayat[$i-1]['warna']) }} opacity-30"></span>
                                @endif
                                <!-- Bulatan -->
                                <span class="z-10 w-5 h-5 rounded-full {{ $item['warna'] }} border-4 border-white shadow-lg absolute left-0 top-1/2 -translate-y-1/2"></span>
                                <!-- Garis bawah (jika bukan item terakhir) -->
                                @if($i < $count - 1)
                                    <span class="absolute left-0 bottom-0 w-1 h-1/2 {{ $item['warna'] }} opacity-30"></span>
                                @endif
                                <div class="ml-10">
                                    <p class="text-sm text-gray-500 mb-1">
                                        {{ $item['tanggal'] }} WIB
                                    </p>
                                    <p class="text-base font-medium text-gray-700">Status berubah dari {{ $item['dari'] }} menjadi {{ $item['ke'] }}.</p>
                                    <p class="text-sm text-gray-500">oleh {{ $item['oleh'] }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="ml-6 text-gray-400">Belum ada riwayat status.</li>
                        @endforelse
                    </ol>
                </div>
                <div class="flex justify-end mt-8">
                    <a href="{{ route('staffumum.statussurat') }}" class="bg-blue-100 text-black rounded-xl px-4 py-1 font-semibold text-sm hover:bg-blue-200 transition">Kembali</a>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection 