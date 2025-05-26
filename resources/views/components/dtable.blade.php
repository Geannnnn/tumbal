@props([
    'id' => 'datatable',
    'columns' => [],
    'data' => [],
    
])

<table id="{{ $id }}" class="w-full bg-transparent text-md text-left text-gray-700">
    <thead class="bg-gray-100">
        <tr>
            <th>No</th>
            @foreach ($columns as $column)
                <th>{{ $column }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                @foreach ($columns as $key => $label)
                    <td>{!! $row[$key] !!}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

@once
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
@endonce

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#{{ $id }}').DataTable({
                pageLength: 5,
                lengthChange: false
            });
        });
    </script>
@endpush
