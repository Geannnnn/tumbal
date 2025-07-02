@props([
    'id' => 'datatable',
    'columns' => [],
    'ajaxUrl' => '',
    'search' => false,
    'ordering' => true,
    'paging' => true,
    'info' => true,
    'lengthMenu' => true,
    'pageLength' => 5,
    'showEdit' => false,
    'showDelete' => false,
    'manualInit' => false
])

@php
    // Normalize columns format
    $normalizedColumns = [];
    foreach ($columns as $key => $column) {
        if (is_array($column)) {
            // Format: ['data' => 'field', 'name' => 'field', 'title' => 'Label']
            $dataKey = $column['data'] ?? $key;
            $title = $column['title'] ?? $column['name'] ?? $dataKey;
            $normalizedColumns[$dataKey] = $title;
        } else {
            // Format: 'field' => 'Label'
            $normalizedColumns[$key] = $column;
        }
    }
@endphp

<table id="{{ $id }}" class="w-full bg-transparent text-md text-left text-gray-700">
    <thead>
        <tr>
            @foreach ($normalizedColumns as $key => $label)
                <th>{{ $label }}</th>
            @endforeach
            @if (($showEdit || $showDelete) && !array_key_exists('actions', $normalizedColumns))
                <th>Aksi</th>
            @endif
        </tr>
    </thead>
    <tbody></tbody>
</table>

<style>
    .dataTables_filter {
        display: none;
    }
    table.dataTable.stripe tbody tr.odd,
    table.dataTable.display tbody tr.odd {
        background-color: transparent !important;
    }
</style>

@if(!$manualInit)
<script>
    $(document).ready(function () {
        var table = $('#{{ $id }}').DataTable({
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
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ $ajaxUrl }}',
                data: function(d) {
                    // Add custom filter parameters
                    d.filter_type = window.currentFilterType || 'tahun';
                    d.year = document.getElementById('year')?.value || new Date().getFullYear();
                    d.month = document.getElementById('month')?.value;
                    d.start_date = document.getElementById('start-date')?.value;
                    d.end_date = document.getElementById('end-date')?.value;
                    
                    // Handle search value safely
                    var searchValue = $('#custom-search').val();
                    if (searchValue && searchValue.trim() !== '') {
                        d.search = { value: searchValue };
                    }
                }
            },
            ordering: {{ $ordering ? 'true' : 'false' }},
            columns: [
                @foreach ($normalizedColumns as $key => $label)
                    {
                        data: '{{ $key }}',
                        name: '{{ $key }}',
                        orderable: {{ $ordering ? 'true' : 'false' }},
                        searchable: true,
                        @if ($key === 'lampiran')
                            render: function (data, type, row) {
                                return data ? `<a href="/storage/${data}" target="_blank" class="flex items-center gap-2 text-blue-800 hover:underline">
                                    <i class="fa-solid fa-cloud-arrow-up text-gray-500"></i>
                                    <span>Lampiran</span>
                                </a>` : '-';
                            }
                        @elseif ($key === 'actions')
                            render: function (data, type, row) {
                                return data;
                            }
                        @endif
                    },
                @endforeach
                @if (($showEdit || $showDelete) && !array_key_exists('actions', $normalizedColumns))
                    {
                        data: null,
                        render: function (data, type, row) {
                            let buttons = '';
                            @if ($showEdit)
                                buttons += `<a href="/edit/${row.id}" class="bg-[#A0D9B7] text-gray-500 px-5 py-1 text-sm font-semibold rounded-lg">Edit</a> `;
                            @endif
                            @if ($showDelete)
                                buttons += `<a href="/delete/${row.id}" class="bg-[#D9A0A1] text-gray-500 py-1 px-4 text-sm font-semibold rounded-lg">Hapus</a>`;
                            @endif
                            return buttons;
                        },
                        orderable: false,
                        searchable: false
                    }
                @endif
            ],
            paging: {{ $paging ? 'true' : 'false' }},
            info: {{ $info ? 'true' : 'false' }},
            pageLength: {{ $pageLength }},
            lengthChange: {{ $lengthMenu ? 'true' : 'false' }},
            drawCallback: function (settings) {
                var api = this.api();
                if (api.column(0).data().length > 0) {
                var pageInfo = api.page.info();
                    api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1 + pageInfo.start;
                });
                }
            }
        });

        // Make table globally accessible
        window.suratTable = table;

        @if ($search)
        $('#custom-search').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('#custom-search').on('input', function () {
            if (this.value === '') {
                table.search('').draw();
            }
        });
        @endif
    });
</script>
@endif