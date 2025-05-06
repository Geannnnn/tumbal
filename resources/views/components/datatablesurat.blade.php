@props([
    'columns' => [],
    'showEdit' => false,
    'showDelete' => false,
    'suratList' => null,
    'search' => false,
])

@if ($search)
    <div class="flex justify-end py-4 pr-5">
        <input type="search" id="custom-search" placeholder="Search..." class="text-black rounded-[10px] bg-[#D9DCE2] caret-black py-2 px-4">
    </div>
@endif


<table id="myTable" class="min-w-full table-auto border-collapse">
    <thead>
        <tr>
            <th>No</th>
            <th>Judul</th>
            <th>Tanggal Pengajuan</th>
            <th>Jenis Surat</th>
            <th>Diajukan Oleh</th>
            <th>Diketuai Oleh</th>
            <th>Anggota</th>
            <th>Dokumen</th>
            <th>Deskripsi</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<style>
    .dataTables_filter {
        display: none;
    }
</style>

@push('scripts')
<script>
    $(document).ready(function () {
        var table = $('#myTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("mahasiswa.pengajuansearch") }}',
            pageLength: 5,
            lengthChange: false,

            language: {
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ entri",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            columns: [
                {
                    data: null,
                    name: 'no',
                    orderable: false,
                    searchable: false
                },
                { data: 'judul_surat', name: 'judul_surat' },
                { data: 'tanggal_pengajuan', name: 'tanggal_pengajuan' },
                { data: 'jenis_surat', name: 'jenis_surat' },
                { data: 'dibuat_oleh', name: 'dibuat_oleh' },
                { data: 'ketua', name: 'ketua' },
                { data: 'anggota', name: 'anggota' },
                { data: 'lampiran', name: 'lampiran', orderable: false, searchable: false },
                { data: 'deskripsi', name: 'deskripsi' },
            ],
            order: [[2, 'desc']],
            drawCallback: function (settings) {
                var api = this.api();
                api.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1 + settings._iDisplayStart;
                });
            }
        });
        $('#custom-search').on('input', function () {
         table.search(this.value).draw();
        });
    });
</script>
@endpush