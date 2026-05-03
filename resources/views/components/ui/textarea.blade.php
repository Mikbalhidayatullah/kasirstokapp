@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'useOld' => true,
])

@if ($label)
    <label @if($name) for="{{ $name }}" @endif class="field-label">{{ $label }}</label>
@endif

<textarea
    @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
    {{ $attributes->merge(['class' => 'field-textarea']) }}
>{{ $useOld && $name ? old($name, $value) : $value }}</textarea>
