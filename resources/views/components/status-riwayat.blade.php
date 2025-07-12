@props([
    'riwayat' => [],
    'showKomentar' => false,
    'routeBack' => '#',
    'judulSurat' => '',
    'jenisSurat' => '',
])

<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow p-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold mb-2">Riwayat status surat</h2>
        @if($judulSurat)
            <p class="text-lg text-gray-600 font-medium">{{ $judulSurat }}</p>
            @if($jenisSurat)
                <p class="text-sm text-gray-500">{{ $jenisSurat }}</p>
            @endif
        @endif
    </div>
    <hr class="mb-6">
    <div class="relative">
        <ol class="ml-6">
            @php
                \Carbon\Carbon::setLocale('id');
                $count = count($riwayat);
            @endphp
            @forelse($riwayat as $i => $item)
                <li class="relative flex min-h-[64px]">
                    @if($i > 0)
                        <span class="absolute left-0 top-0 w-1 h-1/2 {{ $riwayat[$i-1]['warna'] }} opacity-30"></span>
                    @endif
                    <span class="z-10 w-5 h-5 rounded-full {{ $item['warna'] }} border-4 border-white shadow-lg absolute left-0 top-1/2 -translate-y-1/2"></span>
                    @if($i < $count - 1)
                        <span class="absolute left-0 bottom-0 w-1 h-1/2 {{ $item['warna'] }} opacity-30"></span>
                    @endif
                    <div class="ml-10">
                        <p class="text-sm text-gray-500 mb-1">
                            {{ $item['tanggal'] }} WIB
                        </p>
                        <p class="text-base font-medium text-gray-700">Status berubah dari {{ $item['dari'] }} menjadi {{ $item['ke'] }}.</p>
                        <p class="text-sm text-gray-500">oleh {{ $item['oleh'] }}</p>
                        
                        @if($showKomentar && strtolower($item['ke']) === 'ditolak' && !empty($item['komentar']))
                            <div class="mt-2 bg-red-50 border-l-4 border-red-400 p-3 text-sm text-red-700 rounded">
                                <strong>Alasan Penolakan:</strong> {{ $item['komentar'] }}
                            </div>
                        @endif
                    </div>
                </li>
            @empty
                <li class="ml-6 text-gray-400">Belum ada riwayat status.</li>
            @endforelse
        </ol>
    </div>
    <div class="flex justify-end mt-8">
        <a href="{{ $routeBack }}" class="bg-gray-200 text-black rounded-xl px-4 py-1 font-semibold text-sm hover:bg-gray-300 transition">
            Kembali
        </a>
    </div>
</div>
