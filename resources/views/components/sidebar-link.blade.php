@props(['route', 'icon', 'label'])

@php
    $active = Route::currentRouteName() === $route ? 'bg-[#7682d2] text-white' : 'text-[#878A9A]';
@endphp

<a href="{{ route($route) }}"
   class="hover:text-white hover:bg-[#7682d2] rounded-[5px] font-medium py-2 px-4 sm:px-5 md:px-6 lg:px-7 xl:px-8 text-sm sm:text-base md:text-base lg:text-[14.5px] {{ $active }}">
    <i class="fa-solid {{ $icon }} mr-2 sm:mr-3"></i>{{ $label }}
</a>
