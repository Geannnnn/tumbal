@props([
    'title',
    'subtitle',
    'search' => false,
    'searchPlaceholder' => 'Search...'
])

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="pl-15 pt-8">
        <h1 class="font-medium text-[28px]">{{ $title }}</h1>
        <div class="flex justify-between w-full">
            <h1 class="text-[#6D727C] font-medium text-[24px] py-4">{{ $subtitle }}</h1>

            @if (isset($search) && $search)
                <div class="flex justify-end py-4 pr-5">
                    <input type="search" id="custom-search" placeholder="{{ $searchPlaceholder }}" class="text-black rounded-[10px] bg-[#D9DCE2] caret-black py-2 px-4">
                </div>
            @endif
        </div>
    </div>
    <hr class="border-[#DEDBDB]">

    <div class="p-5">
        {{ $slot }}
    </div>

</div>