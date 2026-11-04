@props(['variant' => 'info'])
<div {{ $attributes->class(['alert', 'alert-'.$variant]) }} role="{{ $variant === 'error' ? 'alert' : 'status' }}">
    {{ $slot }}
</div>
