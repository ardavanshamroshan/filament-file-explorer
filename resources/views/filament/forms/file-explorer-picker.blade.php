<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{ open: false }"
        class="space-y-2"
    >
        <x-filament::button type="button" color="gray" @click="open = true">
            @svg('heroicon-o-folder-open', 'h-4 w-4')
            <span>{{ __('filament-file-explorer::file-explorer.browse_files') }}</span>
        </x-filament::button>

        <x-filament::modal id="file-explorer-picker-{{ $getId() }}" width="7xl" :visible="false" x-show="open" @close-modal.window="open = false">
            <x-slot name="heading">
                {{ __('filament-file-explorer::file-explorer.explorer') }}
            </x-slot>

            @livewire('filament-file-explorer.file-explorer', [
                'scopeKey' => $getScopeKey(),
                'rootFolderId' => $getRootFolderId(),
            ], key('picker-'.$getId()))
        </x-filament::modal>
    </div>
</x-dynamic-component>
