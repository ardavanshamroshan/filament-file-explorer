<x-filament-panels::page>
    <livewire:filament-file-explorer::file-explorer
        :scope-key="$this->fileExplorerScopeKey()"
        :root-folder-id="$this->rootFolderId"
        :key="'file-explorer-'.$this->fileExplorerScopeKey().'-'.request()->integer('folder')"
    />
</x-filament-panels::page>
