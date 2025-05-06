@props(['route', 'icon', 'label'])

@php
    $active = Route::currentRouteName() === $route ? 'bg-[#7682d2] text-white' : 'text-[#878A9A]';
@endphp

<a href="{{ route($route) }}" class="hover:text-white hover:bg-[#7682d2] rounded-[5px] font-medium py-2 px-5 {{ $active }}">
    <i class="fa-solid {{ $icon }} mr-3"></i>{{ $label }}
</a>
