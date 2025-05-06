@props([
    'name',
    'id',
    'start' => 2000,
    'end' => date('Y'),
    'descending' => true,
])

<div class="relative flex items-center">
    <i class="fa-solid fa-calendar-days absolute left-3 top-1/2 transform -translate-y-1/2 text-black pointer-events-none"></i>
    <select name="{{ $name }}" id="{{ $id }}"
        {{ $attributes->merge(['class' => 'bg-[#F0F2FF] text-black appearance-none py-3 pl-10 pr-3 rounded-lg w-full']) }}>
    </select>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById(@json($id));
        const start = {{ $start }};
        const end = {{ $end }};
        const descending = @json($descending);

        let years = [];
        if (descending) {
            for (let year = end; year >= start; year--) {
                years.push(year);
            }
        } else {
            for (let year = start; year <= end; year++) {
                years.push(year);
            }
        }

        years.forEach(year => {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            select.appendChild(option);
        });
    });
</script>
@endpush
