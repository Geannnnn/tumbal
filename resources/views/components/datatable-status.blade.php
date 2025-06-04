@props([
    'id' => 'datatable-status',
    'ajaxUrl' => '',
    'search' => false,
    'showEdit' => false,
    'showDelete' => false,
    'columns' => [],
    
])

<table id="{{ $id }}" class="w-full bg-transparent text-sm text-left text-gray-700">
    <thead>
        <tr>
            <th>No</th>
            @foreach ($columns as $label)
                <th>{{ $label }}</th>
            @endforeach
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
  $(document).ready(function () {
    var table = $('#{{ $id }}').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $ajaxUrl }}',
            data: function(d) {
                // Tambahkan search manual ke parameter ajax sesuai struktur DataTables
                d.search.value = $('#custom-search').val();
            }
        },
        pageLength: 5,
        lengthChange: false,
        searching: false, // tetap nonaktifkan search bawaan
        columns: [
            { data: null, name: 'no', orderable: false, searchable: false },
            @foreach (array_keys($columns) as $key)
                { data: '{{ $key }}', name: '{{ $key }}' },
            @endforeach
            {
                data: 'aksi',
                name: 'aksi',
                orderable: false,
                searchable: false
            }
        ],
        drawCallback: function (settings) {
            var api = this.api();
            var pageInfo = api.page.info();
            api.column(0).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + pageInfo.start;
            });
        }
    });

    $('#custom-search').on('keyup', function () {
        table.ajax.reload();  
    });
});

</script>
