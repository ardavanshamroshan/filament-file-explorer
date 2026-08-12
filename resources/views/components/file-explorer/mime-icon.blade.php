@props([
    'icon' => 'file',
    'size' => 'md',
])

@php
    $name = \Ardavan\FilamentFileExplorer\Support\MimeIcon::isKnown((string) $icon) ? (string) $icon : 'file';
    $sizeClass = match ((string) $size) {
        'sm' => 'fe-mime-icon--sm',
        'lg' => 'fe-mime-icon--lg',
        default => 'fe-mime-icon--md',
    };
@endphp

<x-dynamic-component
    :component="'filament-file-explorer::file-explorer.icons.mimes.'.$name"
    {{ $attributes->class([$sizeClass]) }}
/>
