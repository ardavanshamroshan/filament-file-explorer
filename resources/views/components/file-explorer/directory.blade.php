@props([
    'folder',
    'selectedFolders',
    'selectedFiles',
    'renamingType' => null,
    'renamingId' => null,
])

@php
    $isRenaming = $renamingType === 'folder' && (int) $renamingId === (int) $folder->id;
    $itemCount = (int) ($folder->children_count ?? $folder->children()->count())
        + (int) ($folder->file_explorer_count ?? $folder->getMedia(config('filament-file-explorer.collection'))->count());
@endphp

<div
    x-data="{ isDragOver: false }"
    data-id="{{ $folder->id }}"
    data-fe-type="folder"
    data-fe-drop-folder="{{ $folder->id }}"
    @unless($isRenaming)
        x-on:pointerdown="$store.feDrag.pointerDown($event, 'folder', {{ $folder->id }}, @js($folder->name), $wire)"
        x-on:click.stop="
            if ($store.feDrag.consumeClickSuppression()) return;
            $store.feSel.toggle('folder', {{ $folder->id }}, $event.ctrlKey || $event.metaKey);
        "
        x-on:dblclick.stop="$wire.navigateToFolder({{ $folder->id }})"
    @endunless
    x-on:dragover.prevent="
        if ($event.dataTransfer.types.includes('Files')) {
            $event.dataTransfer.dropEffect = 'copy';
            isDragOver = true;
        }
    "
    x-on:dragleave.prevent="isDragOver = false"
    x-on:drop.prevent="$store.feDrag.dropFilesOnFolder($event, {{ $folder->id }}, $wire); isDragOver = false"
    x-on:contextmenu.stop.prevent="
        if (!$store.feSel.hasFolder({{ $folder->id }})) {
            $store.feSel.toggle('folder', {{ $folder->id }}, false);
        }
        $dispatch('fe-context', { type: 'folder', id: {{ $folder->id }}, name: @js($folder->name), x: $event.clientX, y: $event.clientY });
    "
    :class="{
        'fe-drop-target': isDragOver || $store.feDrag.dropTargetId === {{ $folder->id }},
        'is-selected': $store.feSel.hasFolder({{ $folder->id }}),
        'drag-hover': $store.feSel.inMarqueeFolder({{ $folder->id }}),
        'fe-dragging': $store.feDrag.active && $store.feSel.hasFolder({{ $folder->id }}),
    }"
    @class([
        'folder fe-icon-item group relative mx-0.5 flex w-[96px] cursor-default flex-col items-center px-0.5 pt-0.5 pb-0.5 text-center select-none',
        'is-renaming' => $isRenaming,
    ])
>
    <div
        class="fe-icon-well flex h-[68px] w-[76px] shrink-0 items-center justify-center rounded-xl"
        :class="{ 'fe-icon-well--selected': ($store.feSel.hasFolder({{ $folder->id }}) || $store.feSel.inMarqueeFolder({{ $folder->id }})) && !@js($isRenaming) }"
    >
        <x-filament-file-explorer::file-explorer.folder-icon class="h-[3.35rem] w-[3.35rem] drop-shadow-sm" />
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
                :class="{ 'fe-label--selected': $store.feSel.hasFolder({{ $folder->id }}) || $store.feSel.inMarqueeFolder({{ $folder->id }}) }"
                title="{{ $folder->name }}"
            >{{ $folder->name }}</span>
            <span
                class="fe-meta mt-0.5 text-[10px] leading-none text-zinc-400"
                x-show="!($store.feSel.hasFolder({{ $folder->id }}) || $store.feSel.inMarqueeFolder({{ $folder->id }}))"
            >{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
        @endif
    </div>
</div>
