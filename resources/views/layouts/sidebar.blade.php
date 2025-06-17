@php
    $roleRoutePrefix = null;

    if (isset($rolePengusul)) {
        $roleRoutePrefix = match($rolePengusul) {
            1 => 'dosen',
            2 => 'mahasiswa',
            default => null,
        };
    } elseif (isset($staffRole)) {
        $roleRoutePrefix = match($staffRole) {
            'Tata Usaha' => 'tatausaha',
            'Staff Umum' => 'staffumum',
            default => null,
        };
    }

    $authRole = null;
    if (Auth::guard('admin')->check()) $authRole = 'admin';
    elseif (Auth::guard('kepala_sub')->check()) $authRole = 'kepala_sub';
@endphp

<!-- Sidebar -->
<div class="w-[250px] bg-[#F1F2F7] shadow-lg flex flex-col p-4 min-h-screen 
            lg:sticky lg:top-0 xl:sticky xl:top-0 2xl:sticky 2xl:top-0">
    <!-- Logo -->
    <div class="flex items-center">
        <img src="{{ asset('images/surat_logo.svg') }}" alt="logo" class="w-14 h-14 rounded-full mb-2 ml-2">
        <h1 class="text-1xl font-bold text-blue-500 ml-1">Ur Mine</h1>
    </div>

    <!-- Menu -->
    <nav class="flex flex-col space-y-1 h-full">
        <span class="text-[#878A9A] uppercase text-[11px] px-5 mt-6">Menu</span>

        {{-- Pengusul (Dosen / Mahasiswa / Staff) --}}
        @if($roleRoutePrefix)
            <x-sidebar-link route="{{ $roleRoutePrefix }}.dashboard" icon="fa-house" label="Beranda" />

            @switch($roleRoutePrefix)
                @case('dosen')
                @case('mahasiswa')
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.pengajuansurat" icon="fa-file-import fa-bounce" label="Pengajuan Surat" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.draft" icon="fa-file-export fa-spin fa-spin-reverse" label="Draft" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.statussurat" icon="fa-square-check fa-flip" label="Status Surat" />
                    @break

                @case('tatausaha')
                @case('staffumum')
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.statistik" icon="fa-chart-simple fa-bounce" label="Statistik" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.terbitkan" icon="fa-arrow-up-wide-short fa-spin fa-spin-reverse" label="Terbitkan" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.statussurat" icon="fa-square-check fa-flip" label="Status Surat" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.jenissurat" icon="fa-file fa-flip" label="Kelola Jenis Surat" />
                    @break
            @endswitch
        @endif

        {{-- Admin --}}
        @if($authRole === 'admin')
            <x-sidebar-link route="admin.dashboard" icon="fa-house" label="Beranda" />
            <x-sidebar-link route="admin.kelolapengusul" icon="fa-users" label="Kelola Pengusul" />
            <x-sidebar-link route="admin.kelolajenissurat" icon="fa-folder-open" label="Kelola Jenis Surat" />
        @endif

        {{-- Kepala Sub --}}
        @if($authRole === 'kepala_sub')
            <x-sidebar-link route="kepalasub.dashboard" icon="fa-house" label="Beranda" />
        @endif

        {{-- Umum --}}
        <x-sidebar-link route="settings" icon="fa-gear fa-spin" label="Pengaturan" />
        <a href="{{ route('logout') }}" class="hover:text-white hover:bg-red-900 text-[#878A9A] rounded-[5px] font-medium py-2 px-5 mt-auto">
            <i class="fa-solid fa-arrow-right-from-bracket mr-3"></i>Keluar
        </a>
    </nav>
</div>
