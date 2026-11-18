@props(['variant' => 'primary', 'type' => 'button', 'loading' => false, 'disabled' => false, 'loadingLabel' => 'Guardando'])
@php($variant = in_array($variant, ['primary', 'secondary', 'tertiary', 'danger', 'confirmation'], true) ? $variant : 'primary')
<button type="{{ $type }}" {{ $attributes->class(['button', 'button-'.$variant]) }} data-admin-button aria-busy="{{ $loading ? 'true' : 'false' }}" @disabled($disabled || $loading)>
    <span class="button-content">
        <span class="button-label" aria-hidden="{{ $loading ? 'true' : 'false' }}">{{ $slot }}</span>
        <span class="button-loading" aria-hidden="{{ $loading ? 'false' : 'true' }}"><span class="button-spinner" aria-hidden="true"></span>{{ $loadingLabel }}</span>
    </span>
</button>
