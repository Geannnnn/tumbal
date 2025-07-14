@props([
    'riwayat' => [],
    'showKomentar' => false,
    'routeBack' => '#',
    'judulSurat' => '',
    'jenisSurat' => '',
])

<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-md p-8">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900 mb-1">Riwayat Status Surat</h2>
        @if($judulSurat)
            <p class="text-lg text-gray-800 font-semibold leading-tight">{{ $judulSurat }}</p>
            @if($jenisSurat)
                <p class="text-sm text-gray-500">{{ $jenisSurat }}</p>
            @endif
        @endif
    </div>

    <hr class="mb-6 border-gray-200">

    {{-- Timeline --}}
    <div class="relative">
        <div class="absolute left-[10px] top-0 bottom-0 w-0.5 bg-gray-200 z-0"></div>

        <ol class="relative space-y-5 z-10">
            @forelse($riwayat as $i => $item)
                <li class="relative flex items-start">
                    {{-- Titik status --}}
                    <div class="relative z-10">
                        <div class="w-5 h-5 rounded-full border-4 border-white shadow {{ $item['warna'] ?? 'bg-gray-400' }}"></div>
                    </div>

                    {{-- Konten --}}
                    <div class="ml-6">
                        <p class="text-sm text-gray-500 mb-1">
                            {{ $item['tanggal'] }} WIB
                        </p>
                        <p class="text-[15px] text-gray-800 font-medium leading-snug">
                            Status berubah dari 
                            <span class="italic text-gray-600">{{ $item['dari'] }}</span> 
                            menjadi 
                            <span class="italic text-gray-800">{{ $item['ke'] }}</span>.
                        </p>
                        <p class="text-sm text-gray-500 mt-1">oleh {{ $item['oleh'] }}</p>

                        @if($showKomentar && strtolower($item['ke']) === 'ditolak' && !empty($item['komentar']))
                            <div class="mt-3 bg-red-50 border-l-4 border-red-400 px-4 py-2 text-sm text-red-700 rounded-md">
                                <strong>Alasan Penolakan:</strong> {{ $item['komentar'] }}
                            </div>
                        @endif
                    </div>
                </li>
            @empty
                <li class="ml-6 text-gray-400 text-sm">Belum ada riwayat status.</li>
            @endforelse
        </ol>
    </div>

    {{-- Tombol kembali --}}
    <div class="flex justify-end mt-10">
        <a href="{{ $routeBack }}" class="inline-flex items-center gap-2 bg-gray-100 text-gray-700 rounded-xl px-4 py-2 font-medium text-sm hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali
        </a>
    </div>
</div>
