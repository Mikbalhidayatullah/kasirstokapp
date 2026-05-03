@props([
    'label' => null,
    'name' => null,
    'value' => null,
])

@php
    $selected = old($name, $value);
@endphp

@if ($label)
    <label @if($name) for="{{ $name }}" @endif class="field-label">{{ $label }}</label>
@endif

<select @if ($name) name="{{ $name }}" id="{{ $name }}" @endif {{ $attributes->merge(['class' => 'field-select']) }}>
    {{ $slot }}
</select>
