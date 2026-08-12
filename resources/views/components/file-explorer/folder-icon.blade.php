@props([
    'class' => 'h-14 w-14',
])

<img
    src="{{ asset('vendor/filament-file-explorer/folder-macos.webp') }}"
    alt=""
    draggable="false"
    {{ $attributes->class([$class, 'object-contain select-none pointer-events-none']) }}
>
