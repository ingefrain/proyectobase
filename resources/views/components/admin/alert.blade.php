@props(['variant' => 'info'])
@php
    [$label, $icon] = match ($variant) {
        'success' => ['Éxito', '✓'],
        'warning' => ['Advertencia', '!'],
        'error' => ['Error', '×'],
        default => ['Información', 'i'],
    };
@endphp
<div {{ $attributes->class(['alert', 'alert-'.$variant]) }} role="{{ $variant === 'error' ? 'alert' : 'status' }}">
    <span class="alert-icon" aria-hidden="true"><span>{{ $icon }}</span></span>
    <div><span class="sr-only">{{ $label }}: </span>{{ $slot }}</div>
</div>
