@props(['name'])
@error($name)<p class="field-error" id="{{ $name }}-error" role="alert">{{ $message }}</p>@enderror
