@extends('layouts.app')

@section('title', 'Kelola Status Surat')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])

        <main class="flex-1 bg-gray-50 p-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Kelola Status Surat</h1>
                    <button id="add-status-btn" class="bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-800 transition hover:scale-110 cursor-pointer duration-300">
                        <i class="fas fa-plus mr-2"></i>Tambah Status
                    </button>
                </div>

                <x-alertnotif />

                <div class="overflow-x-auto">
                    <table id="status-surat-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 rounded-l-lg">No</th>
                                <th scope="col" class="px-6 py-3">Nama Status</th>
                                <th scope="col" class="px-6 py-3 text-center rounded-r-lg">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statusSurats as $status)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $status->status_surat }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="text-white ml-2 px-5 py-2 rounded-lg hover:cursor-pointer bg-blue-700 hover:bg-blue-800 edit-btn hover:scale-110 transition-all duration-300" data-id="{{ $status->id_status_surat }}" data-name="{{ $status->status_surat }}">Ubah</button>
                                        <form action="{{ route('admin.statussurat.destroy', $status->id_status_surat) }}" method="POST" class="inline-block delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg hover:cursor-pointer hover:scale-110 transition-all duration-300">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="status-modal" class="fixed inset-0 bg-opacity-50 z-50 hidden flex items-center backdrop-blur-sm transition-opacity duration-300 ease-in-out  justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 id="modal-title" class="text-xl font-semibold text-gray-800">Tambah Status Surat</h2>
            <button id="close-modal-btn" class="text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        <form id="status-form" method="POST">
            @csrf
            <div id="method-field"></div>
            <div>
                <label for="status_surat_input" class="block text-sm font-medium text-gray-700 mb-1">Nama Status</label>
                <input type="text" name="status_surat" id="status_surat_input" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="button" id="cancel-btn" class="bg-gray-200 text-gray-800 px-5 py-2 rounded-lg mr-2 hover:bg-gray-300 transition hover:scale-110 cursor-pointer">Batal</button>
                <button type="submit" id="save-btn" class="bg-blue-700 text-white px-5 py-2 rounded-lg hover:bg-blue-800 transition hover:scale-110 cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#status-surat-table').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
        },
        "columnDefs": [
            { "orderable": false, "targets": 2 } // Disable sorting for 'Aksi' column
        ],
        "searching": true,
        "lengthChange": false,
        "pageLength": 10,
        "dom": 'rt<"flex justify-between items-center mt-4"<"text-sm text-gray-600"i><"flex"p>>'
    });

    const modal = document.getElementById('status-modal');
    const addBtn = document.getElementById('add-status-btn');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const form = document.getElementById('status-form');
    const modalTitle = document.getElementById('modal-title');
    const statusInput = document.getElementById('status_surat_input');
    const methodField = document.getElementById('method-field');

    const openModal = () => modal.classList.remove('hidden');
    const closeModal = () => modal.classList.add('hidden');

    addBtn.addEventListener('click', () => {
        form.reset();
        form.action = "{{ route('admin.statussurat.store') }}";
        modalTitle.innerText = 'Tambah Status Surat';
        methodField.innerHTML = '';
        statusInput.value = '';
        openModal();
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            
            form.reset();
            form.action = `/admin/status-surat/${id}`;
            modalTitle.innerText = 'Edit Status Surat';
            statusInput.value = name;
            methodField.innerHTML = `<input type="hidden" name="_method" value="PUT">`;
            openModal();
        });
    });

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Status yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });

    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (event) => {
        if (event.target == modal) {
            closeModal();
        }
    });
});
</script>
@endpush
@endsection 