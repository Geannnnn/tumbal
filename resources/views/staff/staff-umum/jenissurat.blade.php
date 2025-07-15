@extends('layouts.app')

@include('components.alertnotif')

@section('title','Kelola Jenis Surat')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="title-page flex justify-between">
                    <div class="flex justify-start">
                        <h1 class="text-[32px] text-[#1F384C] font-medium">
                            Kelola Jenis Surat
                        </h1>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-4 mt-6">
                    <div class="flex justify-start">
                        <button id="btnShowModal" class="bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-800 transition hover:scale-105 cursor-pointer">Tambah Jenis Surat</button>
                    </div>
                    <div class="flex justify-end">
                        <div class="w-64">
                            <x-form.search 
                                id="searchJenisSurat"
                                name="search"
                                placeholder="Cari jenis surat..."
                                bgColor="#F0F2FF"
                                textColor="black"
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="jenis-surat-table" class="w-full">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Nomor</th>
                                <th class="px-4 py-2 text-left">Jenis Surat</th>
                                <th class="px-4 py-2 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jsdata as $item)
                            <tr>
                                <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2">{{ $item->jenis_surat }}</td>
                                <td class="px-4 py-2">
                                    <button type="button" class="text-white ml-2 px-3 py-1 rounded-lg hover:cursor-pointer bg-blue-700 hover:bg-blue-800 btn-edit hover:scale-110 transition-all duration-300" data-id="{{ $item->id_jenis_surat }}" data-nama="{{ $item->jenis_surat }}">Ubah</button>
                                    <button type="button" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded-lg hover:cursor-pointer btn-hapus hover:scale-110 transition-all duration-300" data-id="{{ $item->id_jenis_surat }}">Hapus</button>
                                    <form id="form-hapus-{{ $item->id_jenis_surat }}" action="{{ route('staffumum.jenissurat.destroy', $item->id_jenis_surat) }}" method="POST" style="display:none;">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Modal Tambah Jenis Surat -->
                <div id="modalTambah" class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-40 backdrop-blur-sm hidden transition-opacity duration-300 ease-in-out">
                    <div class="relative w-full max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-8 animate-modal-show">
                        <button id="btnCloseModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <h2 class="text-2xl font-bold mb-6 text-center">Tambah Jenis Surat</h2>
                        <form action="{{ route('staffumum.jenissurat.store') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <label class="block mb-1 font-semibold text-gray-700">Jenis Surat Baru</label>
                                <input type="text" name="jenis_surat" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" placeholder="Masukkan jenis surat..." required>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" id="btnCancelModal" class="py-2 px-5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition hover:scale-110 cursor-pointer">Batal</button>
                                <button type="submit" class="py-2 px-5 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 transition hover:scale-110 cursor-pointer">Tambah</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Edit Jenis Surat -->
                <div id="modalEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-40 backdrop-blur-sm hidden transition-opacity duration-300 ease-in-out">
                    <div class="relative w-full max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-8 animate-modal-show">
                        <button id="btnCloseEditModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <h2 class="text-2xl font-bold mb-6 text-center">Edit Jenis Surat</h2>
                        <form id="formEditJenisSurat" method="POST" class="space-y-5">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block mb-1 font-semibold text-gray-700">Jenis Surat</label>
                                <input type="text" name="jenis_surat" id="editJenisSuratInput" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                            </div>
                            <div class="flex justify-end gap-2 pt-2">
                                <button type="button" id="btnCancelEditModal" class="py-2 px-5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition hover:scale-110 cursor-pointer">Batal</button>
                                <button type="submit" class="py-2 px-5 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 transition hover:scale-110 cursor-pointer">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if ($.fn.DataTable.isDataTable('#jenis-surat-table')) {
        $('#jenis-surat-table').DataTable().destroy();
    }
    const table = $('#jenis-surat-table').DataTable({
        language: {
            paginate: {
                previous: 'Sebelumnya',
                next: 'Selanjutnya'
            },
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ada data yang cocok'
        },
        dom: 'rt<"flex justify-between items-center mt-4"<"text-sm text-gray-600"i><"flex"p>>',
        pageLength: 5,
        ordering: false,
        responsive: true,
        processing: true,
        searching: true,
        lengthChange: false,
        order: [[0, 'desc']],
        drawCallback: function() {
            attachEventListeners();
        }
    });
    const searchInput = document.getElementById('searchJenisSurat');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            table.search(this.value).draw();
        });
        searchInput.addEventListener('input', function() {
            if (this.value === '') {
                table.search('').draw();
            }
        });
    }
    function attachEventListeners() {
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('btn-edit')) {
                const id = e.target.getAttribute('data-id');
                const nama = e.target.getAttribute('data-nama');
                // Set nilai ke form edit
                const editIdInput = document.getElementById('editJenisSuratInput');
                const modalEdit = document.getElementById('modalEdit');
                const formEdit = document.getElementById('formEditJenisSurat');
                if (editIdInput && modalEdit && formEdit) {
                    editIdInput.value = nama;
                    formEdit.action = `/staff-umum/jenis-surat/${id}`;
                    modalEdit.classList.remove('hidden');
                } else {
                    console.error('Elemen form edit tidak ditemukan');
                }
            }
        });
        // Event listener untuk tombol hapus
        document.querySelectorAll('.btn-hapus').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-hapus-' + id).submit();
                    }
                });
            });
        });
    }
    // Initial attachment of event listeners
    attachEventListeners();
    // Modal functionality
    const btnShowModal = document.getElementById('btnShowModal');
    const modalTambah = document.getElementById('modalTambah');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const btnCancelModal = document.getElementById('btnCancelModal');
    if (btnShowModal && modalTambah) {
        btnShowModal.addEventListener('click', function() {
            modalTambah.classList.remove('hidden');
        });
    }
    if (btnCloseModal && modalTambah) {
        btnCloseModal.addEventListener('click', function() {
            modalTambah.classList.add('hidden');
        });
    }
    if (btnCancelModal && modalTambah) {
        btnCancelModal.addEventListener('click', function() {
            modalTambah.classList.add('hidden');
        });
    }
    // Edit modal functionality
    const btnCloseEditModal = document.getElementById('btnCloseEditModal');
    const btnCancelEditModal = document.getElementById('btnCancelEditModal');
    const modalEdit = document.getElementById('modalEdit');
    if (btnCloseEditModal && modalEdit) {
        btnCloseEditModal.addEventListener('click', function() {
            modalEdit.classList.add('hidden');
        });
    }
    if (btnCancelEditModal && modalEdit) {
        btnCancelEditModal.addEventListener('click', function() {
            modalEdit.classList.add('hidden');
        });
    }
    // Close modals when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === modalTambah) {
            modalTambah.classList.add('hidden');
        }
        if (e.target === modalEdit) {
            modalEdit.classList.add('hidden');
        }
    });
});
</script>
@endpush
@endsection