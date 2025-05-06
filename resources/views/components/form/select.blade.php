@props([
    'name' => '',
    'id' => '',
    'options' => [],
    'placeholder' => 'Pilih opsi',
    'selected' => null,
])

<select name="{{ $name }}" id="{{ $id }}" {{ $attributes->merge(['class' => 'rounded-lg bg-[#F0F2FF] py-3 px-3']) }}>
    <option value="" disabled {{ is_null($selected) ? 'selected' : '' }}>{{ $placeholder }}</option>
    @foreach ($options as $value => $label)
        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>