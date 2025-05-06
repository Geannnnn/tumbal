@props([
        'columns' => [],       // Kolom untuk tabel
        'data' => [],          // Data untuk tabel
        'search' => true,      // fitur search 
        'paging' => true,      // pagination
        'ordering' => true,    // sorting
        'info' => true,        // info
        'pageLength' => 10,    // Default 10
        'showLengthMenu' => true, //menu
        'showEdit' => false,   // Edit
        'showDelete' => false, // Delete
    ])
    
    
    @if ($search ?? true)
        <div class="flex justify-end py-4 pr-5">
            <input type="search" id="custom-search" placeholder="Search..." class="text-black rounded-[10px] bg-[#D9DCE2] caret-black py-2 px-4">
        </div>
    @endif

    <table id="myTable" class="display">
        <thead>
            <tr>
                <th>No</th>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
                @if($showEdit || @$showDelete)
                <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td></td>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                    @if($showEdit || $showDelete)
                    <td>
                        @if($showEdit)
                        {{-- {{ route('data.edit', $row['id']) }} --}}
                            <a href="#" class="bg-[#A0D9B7] text-white px-5 py-1 text-sm font-semibold rounded-lg">Edit</a>
                            <a href="" class="bg-[#D9A0A1] text-white px-5 py-1 text-sm font-semibold rounded-lg">Hapus</a>
                        @endif    
                    </td> {{-- untuk edit --}}
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <style>
        .dataTables_filter {
            display: none;
        }
    </style>
    
    <script>
        $(document).ready(function() {
            var table = $('#myTable').DataTable({
                "paging": {{ $paging ? 'true' : 'false' }},
                "ordering": {{ $ordering ? 'true' : 'false' }},
                "info": {{ $info ? 'true' : 'false' }},
                "pageLength": {{ $pageLength }},
                "lengthChange": {{ $showLengthMenu ? 'true' : 'false' }},
                "columnDefs": [
                {
                    "searchable": false,
                    "orderable": false,
                    "targets": 0
                }
            ],
            "order": [[1, 'asc']],

            });

            table.on('order.dt search.dt draw.dt', function () {
                let i = 1;
                table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function () {
                    this.data(i++);
                });
            }).draw();
    
            $('#custom-search').on('input', function() {
                table.search(this.value).draw();
            });
        });
    </script>