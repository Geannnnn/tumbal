@extends('layouts.app')

@section('title','Kelola Jenis Surat')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        <main class="flex-1 bg-white p-12">
            @yield('content')
            <div class="p-6">
                <div class="mb-4">
                    <h1 class="text-2xl font-semibold">Kelola Jenis Surat</h1>
                </div>

                <div class="flex justify-between items-center mb-4 mt-14">
                <div class="flex justify-start">
                        <button id="btnShowModal" class="bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-800 transition hover:scale-105 cursor-pointer">Tambah Jenis Surat</button>
                    </div>
                    <div class="flex justify-end">
                        <div class="w-64 mr-0">
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
                                    <form id="form-hapus-{{ $item->id_jenis_surat }}" action="{{ route('admin.jenissurat.destroy', $item->id_jenis_surat) }}" method="POST" style="display:none;">
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
                        <form action="{{ route('admin.jenissurat.store') }}" method="POST" class="space-y-5">
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
            @push('scripts')
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM Content Loaded');

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
                                formEdit.action = `/admin/jenis-surat/${id}`;
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

                console.log('Form Edit:', formEdit);

                // Event listener untuk tombol edit
                document.querySelectorAll('.btn-edit').forEach(btn => {
                    btn.addEventListener('click', function() {
                        console.log('Edit button clicked');
                        const id = this.getAttribute('data-id');
                        const nama = this.getAttribute('data-nama');
                        console.log('ID:', id, 'Nama:', nama);
                        inputEdit.value = nama;
                        formEdit.action = `/admin/jenis-surat/${id}`;
                        console.log('Form action set to:', formEdit.action);
                        modalEdit.classList.remove('hidden');
                    });
                });

                // Event listener untuk form edit
                if (formEdit) {
                    console.log('Adding submit event listener to form');
                    formEdit.addEventListener('submit', function(e) {
                        console.log('Form submitted');
                        e.preventDefault();
                        
                        const formData = new FormData(this);
                        const id = this.action.split('/').pop();
                        console.log('Form Data:', Object.fromEntries(formData));
                        console.log('ID:', id);
                        console.log('Action:', this.action);
                        
                        // Ambil CSRF token dari input hidden
                        const csrfToken = document.querySelector('input[name="_token"]').value;
                        console.log('CSRF Token:', csrfToken);
                        
                        // Tambahkan _method untuk Laravel
                        formData.append('_method', 'PUT');
                        
                        fetch(this.action, {
                            method: 'POST', // Tetap gunakan POST karena Laravel akan menangani PUT
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })
                        .then(async response => {
                            console.log('Response status:', response.status);
                            const responseData = await response.text();
                            console.log('Raw response:', responseData);
                            
                            if (!response.ok) {
                                throw new Error(responseData || 'Network response was not ok');
                            }
                            
                            try {
                                return JSON.parse(responseData);
                            } catch (e) {
                                console.error('Error parsing JSON:', e);
                                throw new Error('Invalid JSON response');
                            }
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            // Tutup modal terlebih dahulu
                            modalEdit.classList.add('hidden');
                            // Tampilkan alert setelah modal tertutup
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
                            // Tutup modal terlebih dahulu
                            modalEdit.classList.add('hidden');
                            // Tampilkan alert setelah modal tertutup
                            Swal.fire({
                                title: 'Error!',
                                text: error.message || 'Terjadi kesalahan saat memperbarui jenis surat',
                                icon: 'error'
                            });
                        });
                    });
                } else {
                    console.error('Form edit not found!');
                }

                btnCloseEdit.addEventListener('click', () => modalEdit.classList.add('hidden'));
                btnCancelEdit.addEventListener('click', () => modalEdit.classList.add('hidden'));
                window.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') modalEdit.classList.add('hidden');
                });
            });
            </script>
            @endpush
        </main>
    </div>
</div>

<style>
@keyframes modal-show {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
@keyframes modal-hide {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.95); }
}
.animate-modal-show {
    animation: modal-show 0.3s ease;
}
.animate-modal-hide {
    animation: modal-hide 0.3s ease;
}
</style>
@endsection