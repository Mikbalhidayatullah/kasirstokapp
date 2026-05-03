@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'value' => null,
    'useOld' => true,
])

@if ($label)
    <label @if($name) for="{{ $name }}" @endif class="field-label">{{ $label }}</label>
@endif

<input
    @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
    type="{{ $type }}"
    value="{{ $useOld && $name ? old($name, $value) : $value }}"
    {{ $attributes->merge(['class' => 'field-input']) }}
>
