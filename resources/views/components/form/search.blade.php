@props([
    'id' => null,
    'name' => null,
    'placeholder' => 'Cari...',
    'bgColor' => '#F0F2FF', 
    'textColor' => 'black', 
])

<input type="search"
    name="{{ $name }}"
    id="{{ $id }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'py-2 px-4 rounded-lg outline-none']) }}
    style="background-color: {{ $bgColor }}; color: {{ $textColor }};" />