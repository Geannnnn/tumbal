@extends('layouts.app')

@section('title', 'Draft')

@section('content')

<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-12">
            @yield('content')
            
            <x-backplat
                :title="'Draft'" 
                :subtitle="'Draft Surat Politeknik Negeri Batam'" 
                :search="true"
                :searchPlaceholder="'Cari draft surat...'">
                
                <a href=""></a>
                <x-datatable 
                    id="datatable"
                    :ajaxUrl="route('mahasiswa.draftData')"
                    :columns="[
                        'no' => 'No',
                        'judul_surat' => 'Judul Surat',
                        'action' => 'Aksi'
                    ]"
                    :search="true"
                    :ordering="false"
                    :paging="true"
                    :info="true"
                    :lengthMenu="false"
                    :pageLength="5"
                />
            </x-backplat>

            @push('scripts')
                <script>
                    function hapusSurat(id) {
                        Swal.fire({
                            title: 'Yakin ingin menghapus?',
                            text: "Data yang dihapus tidak bisa dikembalikan!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('form-hapus-' + id).submit();
                            }
                        });
                    }
                </script>
            @endpush

        </main>
    </div>
</div>

@endsection
