@props([
    'id' => 'datatable-status',
    'ajaxUrl' => '',
    'columns' => [],
    'userRole' => null
])

<div class="overflow-x-auto">
    <table id="{{ $id }}" class="w-full text-sm text-left text-gray-700 rounded-3xl shadow bg-transparent">
        <thead>
            <tr>
                <th class="py-4 px-4">No</th>
                @foreach($columns as $key => $label)
                    <th class="py-4 px-4">{{ $label }}</th>
                @endforeach
                <th class="py-4 px-4">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let table = $('#' + '{{ $id }}').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            ajax: {
                url: '{{ $ajaxUrl }}',
                data: function(d) {
                    d.search.value = $('#custom-search').val();
                    d.jenis_surat = $('#jenis_surat').val();
                    d.status_surat = $('#status_surat').val();
                    d.year = $('#year').val();
                }
            },
            pageLength: 5,
            lengthChange: false, // Menyembunyikan menu panjang
            searching: false,
            info: true, // Menampilkan informasi tentang entri yang ditampilkan
            columns: [
                { data: null, orderable: false, searchable: false },
                @foreach($columns as $key => $label)
                    {
                        data: '{{ $key }}',
                        name: '{{ $key }}',
                        @if($key === 'status')
                        render: function(data, type, row) {
                            let badge = '';
                            let text = data;
                            let dot = '';
                            let badgeClass = 'bg-gray-100 text-gray-700';
                            let dotClass = 'bg-gray-400';
                            switch (data.toLowerCase()) {
                                case 'draft':
                                    badgeClass = 'bg-purple-100 text-purple-700';
                                    dotClass = 'bg-purple-600';
                                    break;
                                case 'diajukan':
                                    badgeClass = 'bg-orange-100 text-orange-700';
                                    dotClass = 'bg-orange-500';
                                    break;
                                case 'divalidasi':
                                    badgeClass = 'bg-blue-100 text-blue-700';
                                    dotClass = 'bg-blue-500';
                                    break;
                                case 'menunggu persetujuan':
                                    badgeClass = 'bg-yellow-100 text-yellow-700';
                                    dotClass = 'bg-yellow-500';
                                    break;
                                case 'menunggu penerbitan':
                                    badgeClass = 'bg-lime-100 text-lime-700';
                                    dotClass = 'bg-lime-500';
                                    break;
                                case 'diterbitkan':
                                    badgeClass = 'bg-green-100 text-green-700';
                                    dotClass = 'bg-green-600';
                                    break;
                                case 'ditolak':
                                    badgeClass = 'bg-red-100 text-red-700';
                                    dotClass = 'bg-red-600';
                                    break;
                            }
                            dot = `<span class=\"w-2.5 h-2.5 rounded-full mr-2 ${dotClass} inline-block\"></span>`;
                            badge = `<span class=\"inline-flex items-center rounded-full px-4 py-1 font-semibold text-sm ${badgeClass}\">${dot}${text}</span>`;
                            return badge;
                        }
                        @endif
                    },
                @endforeach
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        let id = row.id || row.id_surat;
                        let baseUrl = '{{ 
                            $userRole === "Dosen" ? "dosen" : 
                            ($userRole === "Staff Umum" ? "staff-umum" : 
                            ($userRole === "Tata Usaha" ? "tata-usaha" : "mahasiswa")) 
                        }}';
                        let url = `/${baseUrl}/statussurat/${id}`;
                        let buttonText = '{{ $userRole === "Staff Umum" ? "Tinjau" : "Detail" }}';
                        return `<button class=\"bg-blue-100 text-black rounded-xl px-4 py-1 font-semibold text-sm hover:bg-blue-200 transition hover:scale-110 cursor-pointer btn-detail-surat\" data-id=\"${id}\" data-url=\"${url}\">${buttonText}</button>`;
                    }
                }
            ],
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            drawCallback: function(settings) {
                var api = this.api();
                api.column(0, {search:'applied', order:'applied'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + settings._iDisplayStart;
                });
                $('.btn-detail-surat').off('click').on('click', function(e) {
                    e.preventDefault();
                    const url = $(this).data('url');
                    window.location.href = url;
                });
            },
            dom: 'tip' // Menampilkan informasi dan pagination
        });

        $('#custom-search').on('input', function () {
            table.ajax.reload();  
        });
    });
</script>
@endpush
