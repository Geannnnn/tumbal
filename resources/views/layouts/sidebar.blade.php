    @php
        $roleRoutePrefix = null;

        if (isset($rolePengusul)) {
            $roleRoutePrefix = $rolePengusul == 1 ? 'dosen' : ($rolePengusul == 2 ? 'mahasiswa' : null);
        } elseif (isset($staffRole)) {
            $roleRoutePrefix = $staffRole == 'Tata Usaha' ? 'tatausaha' : ($staffRole == 'Staff Umum' ? 'staffumum' : null);
        }
    @endphp
   <!-- Sidebar -->
    <div class="w-[250px] bg-[#F1F2F7] shadow-lg flex flex-col p-4 min-h-screen">
        <div class="flex items-center">
            <img src="{{ asset('images/surat_logo.svg') }}" alt="logo" class="w-14 h-14 rounded-full mb-2 ml-2">
            <h1 class="text-1xl font-bold text-blue-500 ml-1">Ur Mine</h1>
        </div>
        
        <nav class="flex flex-col space-y-1 h-full">
            <span class="text-[#878A9A] uppercase text-[11px] px-5 mt-6">Menu</span>
        
            {{-- Mahasiswa --}}
            @if($roleRoutePrefix)
                <x-sidebar-link route="{{ $roleRoutePrefix }}.dashboard" icon="fa-house fa-shake" label="Dashboard" />

                @if(in_array($roleRoutePrefix, ['dosen', 'mahasiswa']))
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.pengajuansurat" icon="fa-file-import fa-bounce" label="Pengajuan Surat" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.draft" icon="fa-file-export fa-spin fa-spin-reverse" label="Draft" />
                    <x-sidebar-link route="{{ $roleRoutePrefix }}.statussurat" icon="fa-square-check fa-flip" label="Status Surat" />
                @endif

                @if(in_array($roleRoutePrefix, ['tatausaha', 'staffumum']))
                <x-sidebar-link route="tatausaha.dashboard" icon="fa-chart-simple fa-bounce" label="Statistik" />
                <x-sidebar-link route="tatausaha.dashboard" icon="fa-arrow-up-wide-short fa-spin fa-spin-reverse" label="Terbitkan" />
                <x-sidebar-link route="tatausaha.dashboard" icon="fa-square-check fa-flip" label="Status Surat" />
                <x-sidebar-link route="tatausaha.dashboard" icon="fa-file fa-flip" label="Kelola Jenis Surat" />
                @endif
            @endif
            
            {{-- Tata Usaha --}}
            @if(isset($roleStaff) && $roleStaff === 'Tata Usaha')
                <x-sidebar-link route="tatausaha.dashboard" icon="fa-house" label="Dashboard" />
                {{-- <x-sidebar-link route="tatausaha.dashboard" icon="fa-house" label="Statistik" /> --}}
        
            {{-- Staff Umum --}}
            @elseif(isset($roleStaff) && $roleStaff === 'Staff Umum')
                <x-sidebar-link route="staffumum.dashboard" icon="fa-house" label="Dashboard" />
                {{-- <x-sidebar-link route="tatausaha.dashboard" icon="fa-house" label="Statistik" /> --}}
                {{-- <x-sidebar-link route="tatausaha.dashboard" icon="fa-house" label="Terbitkan" /> --}}
                {{-- <x-sidebar-link route="tatausaha.dashboard" icon="fa-house" label="Status Surat" /> --}}
                {{-- <x-sidebar-link route="tatausaha.dashboard" icon="fa-house" label="Kelola Jenis Surat" /> --}}
            @endif
            <a href="{{ route('logout') }}" class="hover:text-white hover:bg-red-900 text-[#878A9A] rounded-[5px] font-medium py-2 px-5">
                <i class="fa-solid fa-gear fa-spin mr-3"></i>Setting
            </a>
            <a href="{{ route('logout') }}" class="hover:text-white hover:bg-red-900 text-[#878A9A] rounded-[5px] font-medium py-2 px-5 mt-auto">
                <i class="fa-solid fa-arrow-right-from-bracket mr-3"></i>Keluar
            </a>
        </nav>
    </div>
