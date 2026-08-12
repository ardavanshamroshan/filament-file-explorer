@props([
    'media',
    'selectedFiles',
    'selectedFolders' => [],
    'key',
    'openUrl' => null,
    'previewUrl' => null,
    'renamingType' => null,
    'renamingId' => null,
])

@php
    $isRenaming = $renamingType === 'file' && (int) $renamingId === (int) $media->id;
    $label = $media->name ?: $media->file_name;
    $isImage = str_starts_with((string) $media->mime_type, 'image/');
    $thumb = null;
    if ($isImage) {
        $thumb = $media->hasGeneratedConversion('thumbnail')
            ? $media->getUrl('thumbnail')
            : ($previewUrl ?: null);
    }
    $mimeIcon = (function_exists('getFileType') ? getFileType($media->mime_type) : null) ?: 'file';
@endphp

<div
    x-data
    data-id="{{ $media->id }}"
    data-fe-type="file"
    id="{{ $key }}"
    @unless($isRenaming)
        x-on:pointerdown="$store.feDrag.pointerDown($event, 'file', {{ $media->id }}, @js($label), $wire)"
        x-on:click.stop="
            if ($store.feDrag.consumeClickSuppression()) return;
            $store.feSel.toggle('file', {{ $media->id }}, $event.ctrlKey || $event.metaKey);
        "
        @if($openUrl)
            x-on:dblclick.stop="window.open(@js($openUrl), '_blank')"
        @endif
    @endunless
    x-on:contextmenu.stop.prevent="
        if (!$store.feSel.hasFile({{ $media->id }})) {
            $store.feSel.toggle('file', {{ $media->id }}, false);
        }
        $dispatch('fe-context', { type: 'file', id: {{ $media->id }}, name: @js($label), x: $event.clientX, y: $event.clientY });
    "
    :class="{
        'is-selected': $store.feSel.hasFile({{ $media->id }}),
        'drag-hover': $store.feSel.inMarqueeFile({{ $media->id }}),
        'fe-dragging': $store.feDrag.active && $store.feSel.hasFile({{ $media->id }}),
    }"
    @class([
        'file fe-icon-item group relative mx-0.5 flex w-[96px] cursor-default flex-col items-center px-0.5 pt-0.5 pb-0.5 text-center select-none',
        'is-renaming' => $isRenaming,
    ])
>
    <div
        class="fe-icon-well flex h-[68px] w-[76px] shrink-0 items-center justify-center rounded-xl"
        :class="{ 'fe-icon-well--selected': ($store.feSel.hasFile({{ $media->id }}) || $store.feSel.inMarqueeFile({{ $media->id }})) && !@js($isRenaming) }"
    >
        @if ($thumb)
            <img
                src="{{ $thumb }}"
                alt=""
                draggable="false"
                loading="lazy"
                decoding="async"
                class="pointer-events-none max-h-[48px] max-w-[56px] rounded-md object-contain"
            >
        @else
            @svg('heroicon-o-document', 'h-12 w-12 text-zinc-400')
        @endif
    </div>

    <div class="fe-caption relative mt-1.5 flex w-full flex-col items-center px-0.5">
        @if ($isRenaming)
            <input
                type="text"
                id="rename-input"
                wire:model="renameValue"
                wire:keydown.enter.prevent="saveRename"
                wire:keydown.escape.prevent="cancelRename"
                wire:blur="saveRename"
                class="fe-rename-input fe-input w-full px-1 py-0.5 text-center text-[11px] leading-tight"
                @click.stop
                @pointerdown.stop
            >
        @else
            <span
                class="fe-label dark:text-zinc-100"
                :class="{ 'fe-label--selected': $store.feSel.hasFile({{ $media->id }}) || $store.feSel.inMarqueeFile({{ $media->id }}) }"
                title="{{ $label }}"
            >{{ $label }}</span>
        @endif
    </div>
</div>
