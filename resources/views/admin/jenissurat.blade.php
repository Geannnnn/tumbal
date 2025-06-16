@extends('layouts.app')

@include('components.alertnotif')

@section('title','Kelola Jenis Surat')

@section('content')
<x-alertnotif />
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-white p-12">
            @yield('content')
             <div class="title-page flex justify-between items-center mb-6">
                <div class="flex justify-start">
                    <h1 class="text-[32px] text-[#1F384C] font-medium">
                        Kelola Jenis Surat
                    </h1>
                </div>
                <button id="btnShowModal" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Tambah Jenis Surat</button>
             </div>

            <!-- Modal Tambah Jenis Surat -->
            <div id="modalTambah" class="fixed inset-0 z-50 flex items-center justify-center bg-black-100 bg-opacity-90 hidden">
                <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md relative">
                    <button id="btnCloseModal" class="absolute top-2 right-3 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Tambah Jenis Surat</h2>
                    <form action="{{ route('admin.jenissurat.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block mb-1 font-medium">Jenis Surat Baru</label>
                            <input type="text" name="jenis_surat" class="border p-2 rounded w-full" placeholder="Masukkan jenis surat..." required>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="btnCancelModal" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Edit Jenis Surat -->
            <div id="modalEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black-100 bg-opacity-90 hidden">
                <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-md relative">
                    <button id="btnCloseEditModal" class="absolute top-2 right-3 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
                    <h2 class="text-xl font-bold mb-4">Edit Jenis Surat</h2>
                    <form id="formEditJenisSurat" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block mb-1 font-medium">Jenis Surat</label>
                            <input type="text" name="jenis_surat" id="editJenisSuratInput" class="border p-2 rounded w-full" required>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="btnCancelEditModal" class="px-4 py-2 rounded bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <x-dtable 
                id="jenis-surat-table"
                :columns="['jenis_surat' => 'Jenis Surat', 'aksi' => 'Aksi']"
                :data="$jsdata->map(function($item) {
                    return [
                        'jenis_surat' => $item->jenis_surat,
                        'aksi' => '
                            <button type=\'button\' class=\'text-red-600 btn-hapus\' data-id=\''.$item->id_jenis_surat.'\'>Hapus</button>
                            <button type=\'button\' class=\'text-blue-600 ml-2 btn-edit\' data-id=\''.$item->id_jenis_surat.'\' data-nama=\''.$item->jenis_surat.'\'>Edit</button>
                            <form id=\'form-hapus-'.$item->id_jenis_surat.'\' action=\''.route('admin.jenissurat.destroy', $item->id_jenis_surat).'\' method=\'POST\' style=\'display:none;\'>
                                <input type=\'hidden\' name=\'_token\' value=\''.csrf_token().'\'>
                                <input type=\'hidden\' name=\'_method\' value=\'DELETE\'>
                            </form>'
                    ];
                })"
            />
            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Modal tambah
                const modal = document.getElementById('modalTambah');
                const btnShow = document.getElementById('btnShowModal');
                const btnClose = document.getElementById('btnCloseModal');
                const btnCancel = document.getElementById('btnCancelModal');
                btnShow.addEventListener('click', () => modal.classList.remove('hidden'));
                btnClose.addEventListener('click', () => modal.classList.add('hidden'));
                btnCancel.addEventListener('click', () => modal.classList.add('hidden'));
                window.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') modal.classList.add('hidden');
                });

                // Modal edit
                const modalEdit = document.getElementById('modalEdit');
                const btnCloseEdit = document.getElementById('btnCloseEditModal');
                const btnCancelEdit = document.getElementById('btnCancelEditModal');
                const formEdit = document.getElementById('formEditJenisSurat');
                const inputEdit = document.getElementById('editJenisSuratInput');

                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const nama = this.getAttribute('data-nama');
                        inputEdit.value = nama;
                        formEdit.action = `/admin/jenis-surat/${id}`;
                        modalEdit.classList.remove('hidden');
                    });
                });

                // Hapus event listener lama jika ada
                const oldFormEdit = formEdit.cloneNode(true);
                formEdit.parentNode.replaceChild(oldFormEdit, formEdit);

                // Tambahkan event listener baru
                oldFormEdit.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Jenis surat berhasil diperbarui',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat memperbarui jenis surat',
                            icon: 'error'
                        });
                    });
                });

                btnCloseEdit.addEventListener('click', () => modalEdit.classList.add('hidden'));
                btnCancelEdit.addEventListener('click', () => modalEdit.classList.add('hidden'));
                window.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') modalEdit.classList.add('hidden');
                });

                // SweetAlert hapus
                document.querySelectorAll('.btn-hapus').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        Swal.fire({
                            title: 'Apakah anda yakin ingin menghapus data ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('form-hapus-' + id).submit();
                            }
                        });
                    });
                });
            });
            </script>
            @endpush
        </main>
    </div>
</div>
@endsection