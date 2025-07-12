@extends('layouts.app')

@section('title', 'Kelola Pengusul')

@section('content')
<x-alertnotif />
<div class="flex h-screen ml-[250px] overflow-x-hidden ">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])


        <main class="flex-1 bg-white p-4 sm:p-6 md:p-8 lg:p-12">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Kelola Pengusul</h1>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="mb-4 flex justify-between gap-2">
                        <button onclick="showModal('create')" class="py-2 px-4 rounded-[10px] bg-blue-700 text-white hover:cursor-pointer hover:scale-105 transition-all duration-300">Tambah Pengusul</button>
                        <x-form.search name="custom-search" placeholder="Cari pengusul..." />
                    </div>
                    <table id="pengusul-table" class="w-full">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>NIM</th>
                                <th>NIP</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<x-admin.pengusul-form-modal />

@push('scripts')
<script>
let deleteId = null;
let table;

$(document).ready(function() {
    table = $('#pengusul-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.pengusul.data') }}",
        pageLength: 5,
        dom: 'rtip', // Hilangkan search dan lengthMenu bawaan
        columns: [
            { 
                data: null,
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'nama', name: 'nama' },
            { data: 'email', name: 'email' },
            { data: 'nim', name: 'nim' },
            { data: 'nip', name: 'nip' },
            {
                data: 'role',
                name: 'role',
                render: function(data, type, row) {
                    if (typeof row.role === 'object' && row.role !== null) {
                        return row.role.role;
                    }
                    return row.role || '-';
                }
            },
            {
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="showModal('edit', ${row.id})" class="py-2 px-4 rounded-[10px] bg-blue-700 text-white hover:cursor-pointer hover:scale-110 transition-all duration-300">Ubah</button>
                            <button type="button" onclick="showDeleteModal(${row.id})" class="py-2 px-4 rounded-[10px] bg-red-700 text-white hover:cursor-pointer hover:scale-110 transition-all duration-300">Hapus</button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            processing: '<div class="flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div><span class="ml-2">Memproses...</span></div>',
            zeroRecords: '<div class="text-center py-4">Tidak ada data yang cocok</div>',
            emptyTable: '<div class="text-center py-4">Tidak ada data yang tersedia</div>',
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "",
            loadingRecords: '<div class="flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-700"></div><span class="ml-2">Memuat...</span></div>',
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Custom search
    $("input[name='custom-search']").on('keyup change', function() {
        table.search(this.value).draw();
    });
});

function showModal(type, id = null) {
    const modal = document.getElementById('pengusul-modal');
    const form = document.getElementById('pengusulForm');
    const title = document.getElementById('modalTitle');
    
    // Reset form
    form.reset();
    document.querySelectorAll('[id$="-error"]').forEach(el => {
        el.classList.add('hidden');
        el.textContent = '';
    });
    
    if (type === 'create') {
        title.textContent = 'Tambah Pengusul';
        document.getElementById('pengusul_id').value = '';
        document.getElementById('password').required = true;
        document.getElementById('role').value = '';
    } else {
        title.textContent = 'Edit Pengusul';
        document.getElementById('pengusul_id').value = id;
        document.getElementById('password').required = false;
        // Fetch pengusul data
        fetch(`/admin/pengusul/${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('nama').value = data.data.nama;
                    document.getElementById('email').value = data.data.email;
                    document.getElementById('role').value = data.data.id_role_pengusul;
                    document.getElementById('nim').value = data.data.nim || '';
                    document.getElementById('nip').value = data.data.nip || '';
                    toggleIdentifierField();
                } else {
                    Swal.fire({icon:'error',title:'Gagal',text:data.message||'Gagal mengambil data pengusul'});
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({icon:'error',title:'Gagal',text:'Terjadi kesalahan saat mengambil data pengusul'});
            });
    }
    modal.classList.remove('hidden');
    toggleIdentifierField();
}

function hideModal() {
    document.getElementById('pengusul-modal').classList.add('hidden');
}

function showDeleteModal(id) {
    deleteId = id;
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data yang dihapus tidak dapat dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            deletePengusul();
        }
    });
}

function hideDeleteModal() {
    deleteId = null;
}

function toggleIdentifierField() {
    const roleSelect = document.getElementById('role');
    const role = roleSelect.value;
    const nimField = document.getElementById('nim-field');
    const nipField = document.getElementById('nip-field');
    nimField.classList.add('hidden');
    nipField.classList.add('hidden');
    if (role === '2') {
        nimField.classList.remove('hidden');
    } else if (role === '1') {
        nipField.classList.remove('hidden');
    }
}

document.getElementById('role').addEventListener('change', function() {
    document.getElementById('id_role_pengusul').value = this.value;
});

document.getElementById('pengusulForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    const url = id ? `/admin/pengusul/${id}` : "/admin/pengusul";
    const method = id ? 'PUT' : 'POST';
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideModal();
            table.ajax.reload(null, false);
            Swal.fire({icon:'success',title:'Berhasil',text:data.message});
        } else if (data.errors) {
            Object.keys(data.errors).forEach(key => {
                const errorElement = document.getElementById(`${key}-error`);
                if (errorElement) {
                    errorElement.textContent = data.errors[key][0];
                    errorElement.classList.remove('hidden');
                }
            });
        } else {
            Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan'});
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({icon:'error',title:'Gagal',text:'Terjadi kesalahan saat menyimpan data'});
    });
});

function deletePengusul() {
    if (!deleteId) return;
    fetch(`/admin/pengusul/${deleteId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            table.ajax.reload(null, false);
            Swal.fire({icon:'success',title:'Berhasil',text:data.message});
        } else {
            Swal.fire({icon:'error',title:'Gagal',text:data.message||'Terjadi kesalahan saat menghapus data'});
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({icon:'error',title:'Gagal',text:'Terjadi kesalahan saat menghapus data'});
    });
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

<style>
.dataTables_wrapper .dataTables_info {
    padding-top: 1rem;
    color: #4b5563;
}
.dataTables_wrapper .dataTables_paginate {
    padding-top: 1rem;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border-radius: 0.375rem;
    border: 1px solid #e2e8f0;
    background: white;
    color: #4b5563 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #2563eb !important;
    color: white !important;
    border-color: #2563eb;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f3f4f6 !important;
    border-color: #e2e8f0;
    color: #4b5563 !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #1d4ed8 !important;
    color: white !important;
}
.dataTables_wrapper .dataTables_processing {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}
</style>
@endpush