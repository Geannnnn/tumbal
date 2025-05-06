@extends('layouts.app')

@section('title', 'Draft')

@section('content')

<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')
            <x-alertnotif />
            <x-backplat
                :title="'Draft'" 
                :subtitle="'Draft Surat Politeknik Negeri Batam'" 
                :search="true">
                <a href=""></a>
                <table id="datatable" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Id Surat</th>
                            <th>Judul Surat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </x-backplat>


            <style>
                .dataTables_filter {
                    display: none;
                }
            </style>
            @push('scripts')
                <script>
                    $(document).ready(function() {
                        $('#datatable').DataTable({
                            pageLength:false,
                            lengthChange:false,
                            processing: true,
                            serverSide: true,
                            ajax: '{{ route('mahasiswa.draftData') }}', // Pastikan URL ini sesuai dengan rute yang Anda buat
                            columns: [
                                { data: null, searchable: false, render: function (data, type, row, meta) {
                                    return meta.row + 1;
                                }},
                                { data: 'id_surat', visible: false },
                                { data: 'judul_surat' },
                                { data: 'action', orderable: false, searchable: false }, 
                            ],
                        });
                    });
                    $('#custom-search').on('keyup', function () {
                        table.search(this.value).draw();
                    });

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
