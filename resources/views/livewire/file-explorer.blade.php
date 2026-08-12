<div>
    @if(!$currentFolder)
        <div class="p-8 text-center text-zinc-500">{{ __('filament-file-explorer::file-explorer.empty') }}</div>
    @else
        @php
            $sortedFolders = $this->sortedFolders(collect($folders));
            $fileCollection = collect($searchedFiles ?: $currentFolder->getMedia(config('filament-file-explorer.collection')));
            $sortedMedia = $this->sortedFiles($fileCollection);
            $abilities = $this->abilities();
            $locale = str_replace('_', '-', app()->getLocale());
            $i18n = trans('filament-file-explorer::file-explorer');
        @endphp
        <div
            class="w-full"
            x-data="FileExplorerUi({
                scopeKey: @js($scopeKey),
                rootFolderId: {{ $rootFolderId }},
                abilities: @js($abilities),
                mediaUrlBase: @js(url(config('filament-file-explorer.routes.prefix'))),
                translations: @js($i18n),
                selectedFolders: @js($selectedFolders),
                selectedFiles: @js($selectedFiles),
            })"
            x-on:livewire-upload-start="onUploadStart()"
            x-on:livewire-upload-finish="onUploadFinish()"
            x-on:livewire-upload-cancel="onUploadCancel()"
            x-on:livewire-upload-error="onUploadError()"
            x-on:livewire-upload-progress="onUploadProgress($event.detail.progress)"
            @fe-upload-settled.window="onUploadSettled()"
            @fe-context.window="openContext($event.detail)"
            @fe-item-drag-start.window="onItemDragStart()"
            @fe-item-drag-end.window="isDraggingItems = false"
            @fe-upload-files.window="uploadDroppedFiles($event.detail.files)"
            @fe-sel-cleared.window="Alpine.store('feSel').replace([], [])"
            @fe-folder-created.window="Alpine.store('feSel').replace([Number($event.detail.folderId)], [])"
            @click.window="closeContext()"
            @keydown.escape.window="closeContext(); $wire.cancelRename(); $wire.cancelNewFolder(); $wire.closeInfo()"
        >
            <div class="fe-finder w-full overflow-hidden rounded-xl border border-zinc-200/90 bg-zinc-50/80 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/40" dir="ltr" lang="{{ $locale }}" translate="no">
                <div class="flex min-h-[560px]">
                    {{-- Sidebar --}}
                    <aside
                        class="fe-sidebar hidden shrink-0 flex-col border-e border-zinc-200/80 bg-zinc-50/90 dark:border-zinc-700 dark:bg-zinc-900/50 sm:flex"
                        x-bind:class="$store.feUi.sidebarOpen ? 'fe-sidebar--open' : 'fe-sidebar--closed'"
                    >
                        <div class="flex items-center gap-2 border-b border-zinc-200/80 px-2.5 py-2 dark:border-zinc-700" x-show="$store.feUi.sidebarOpen" x-cloak>
                            @svg('heroicon-o-folder', 'h-5 w-5 text-zinc-500')
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ __('filament-file-explorer::file-explorer.explorer') }}</span>
                        </div>
                        <div class="flex-1 overflow-y-auto overflow-x-hidden px-1.5 py-2" x-show="$store.feUi.sidebarOpen" x-cloak>
                            <x-filament-file-explorer::file-explorer.sidebar-tree
                                :nodes="$this->sidebarTree()"
                                :current-id="$currentFolder->id"
                            />
                        </div>
                    </aside>

                    <div class="flex min-w-0 flex-1 flex-col">
                {{-- Toolbar --}}
                <div class="fe-toolbar border-b border-zinc-200/80 px-2.5 py-1.5 dark:border-zinc-700/80" dir="ltr">
                    <div class="fe-toolbar__primary flex shrink-0 items-center gap-0.5">
                        <button type="button" class="fe-tool-btn hidden sm:inline-flex" title="{{ __('filament-file-explorer::file-explorer.toolbar.toggle_sidebar') }}" @click="$store.feUi.sidebarOpen = !$store.feUi.sidebarOpen">
                            <span x-show="$store.feUi.sidebarOpen">@svg('heroicon-o-bars-3', 'h-4 w-4')</span>
                            <span x-show="!$store.feUi.sidebarOpen" x-cloak>@svg('heroicon-o-bars-3-bottom-left', 'h-4 w-4')</span>
                        </button>
                        <div class="mx-1 hidden h-4 w-px bg-zinc-200 sm:block dark:bg-zinc-600"></div>
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.back') }}" @disabled(!$this->canGoBack()) wire:click="goBack">
                            @svg('heroicon-o-chevron-left', 'h-4 w-4')
                        </button>
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.forward') }}" @disabled(!$this->canGoForward()) wire:click="goForward">
                            @svg('heroicon-o-chevron-right', 'h-4 w-4')
                        </button>
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.up') }}" @disabled((int) $this->currentFolder->id === (int) $this->rootFolderId) wire:click="navigateToParent">
                            @svg('heroicon-o-arrow-uturn-up', 'h-4 w-4')
                        </button>

                        <div class="mx-1 h-4 w-px bg-zinc-200 dark:bg-zinc-600"></div>

                        @if ($abilities['mkdir'])
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.new_folder') }}" wire:click="createNewFolder">
                            @svg('heroicon-o-folder-plus', 'h-4 w-4')
                        </button>
                        @endif

                        @if ($abilities['upload'])
                        <input
                            type="file"
                            name="files"
                            id="fileInput"
                            multiple
                            class="hidden"
                            accept="{{ $this->acceptAttribute() }}"
                            wire:key="file-input-{{ $fileInputKey }}"
                            @change="pickAndUploadFiles($event)"
                        >

                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.upload') }}" @click="document.getElementById('fileInput').click()">
                            @svg('heroicon-o-arrow-up-tray', 'h-4 w-4')
                        </button>
                        @endif
                    </div>

                    <div class="fe-toolbar__secondary fe-toolbar__collapse flex shrink-0 items-center gap-0.5">
                        <div class="mx-1 h-4 w-px bg-zinc-200 dark:bg-zinc-600"></div>

                        @if ($abilities['rename'])
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.rename') }}" x-cloak
                            x-show="($store.feSel.folders.length + $store.feSel.files.length) === 1"
                            @click="toolbarRename()">
                            @svg('heroicon-o-pencil', 'h-3.5 w-3.5')
                        </button>
                        @endif
                        @if ($abilities['download'])
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.download') }}" x-cloak
                            x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0"
                            @click="toolbarDownload()">
                            @svg('heroicon-o-arrow-down-tray', 'h-3.5 w-3.5')
                        </button>
                        @endif
                        @if ($abilities['copy'])
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.copy') }}" x-cloak
                            x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0"
                            @click="toolbarCopy()">
                            @svg('heroicon-o-document-duplicate', 'h-3.5 w-3.5')
                        </button>
                        @endif
                        @if ($abilities['move'] || $abilities['copy'])
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.paste') }}" :disabled="!$wire.clipboardReady" @click="$wire.pasteClipboard()">
                            @svg('heroicon-o-clipboard-document', 'h-3.5 w-3.5')
                        </button>
                        @endif
                        @if ($abilities['getInfo'])
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.info') }}" x-cloak
                            x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0"
                            @click="toolbarInfo()">
                            @svg('heroicon-o-information-circle', 'h-3.5 w-3.5')
                        </button>
                        @endif
                        @if ($abilities['delete'] || $abilities['deleteFolder'])
                        <button type="button" class="fe-tool-btn fe-tool-btn--ghost-danger" title="{{ __('filament-file-explorer::file-explorer.toolbar.delete') }}" x-cloak
                            x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0"
                            @click="confirmDeleteSelected()">
                            @svg('heroicon-o-trash', 'h-3.5 w-3.5')
                        </button>
                        @endif
                    </div>

                    <div class="fe-toolbar__more relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.more_actions') }}" @click="open = !open">
                            @svg('heroicon-o-ellipsis-vertical', 'h-4 w-4')
                        </button>
                        <div
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fe-menu absolute start-0 top-full z-30 mt-1 w-48 overflow-hidden py-1"
                        >
                            @if ($abilities['rename'])
                            <button type="button" class="fe-view-item" x-show="($store.feSel.folders.length + $store.feSel.files.length) === 1" @click="toolbarRename(); open=false">
                                @svg('heroicon-o-pencil', 'h-3.5 w-3.5') {{ __('filament-file-explorer::file-explorer.toolbar.rename') }}
                            </button>
                            @endif
                            @if ($abilities['download'])
                            <button type="button" class="fe-view-item" x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0" @click="toolbarDownload(); open=false">
                                @svg('heroicon-o-arrow-down-tray', 'h-3.5 w-3.5') {{ __('filament-file-explorer::file-explorer.toolbar.download') }}
                            </button>
                            @endif
                            @if ($abilities['copy'])
                            <button type="button" class="fe-view-item" x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0" @click="toolbarCopy(); open=false">
                                @svg('heroicon-o-document-duplicate', 'h-3.5 w-3.5') {{ __('filament-file-explorer::file-explorer.toolbar.copy') }}
                            </button>
                            @endif
                            @if ($abilities['move'] || $abilities['copy'])
                            <button type="button" class="fe-view-item" :disabled="!$wire.clipboardReady" @click="$wire.pasteClipboard(); open=false">
                                @svg('heroicon-o-clipboard-document', 'h-3.5 w-3.5') {{ __('filament-file-explorer::file-explorer.toolbar.paste') }}
                            </button>
                            @endif
                            @if ($abilities['getInfo'])
                            <button type="button" class="fe-view-item" x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0" @click="toolbarInfo(); open=false">
                                @svg('heroicon-o-information-circle', 'h-3.5 w-3.5') {{ __('filament-file-explorer::file-explorer.toolbar.info') }}
                            </button>
                            @endif
                            @if ($abilities['delete'] || $abilities['deleteFolder'])
                            <button type="button" class="fe-view-item text-red-600 dark:text-red-400" x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0" @click="confirmDeleteSelected(); open=false">
                                @svg('heroicon-o-trash', 'h-3.5 w-3.5') {{ __('filament-file-explorer::file-explorer.toolbar.delete') }}
                            </button>
                            @endif
                        </div>
                    </div>

                    <div class="fe-toolbar__spacer min-w-0 flex-1"></div>

                    <div class="fe-toolbar__end flex shrink-0 items-center gap-1">
                        <span class="me-1 hidden text-[11px] text-zinc-400 sm:inline" x-cloak
                              x-show="($store.feSel.folders.length + $store.feSel.files.length) > 0"
                              x-text="($store.feSel.folders.length + $store.feSel.files.length) + ' selected'"></span>

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.sort_by') }}" @click="open = !open">
                                @svg('heroicon-o-arrows-up-down', 'h-3.5 w-3.5')
                            </button>
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fe-menu absolute end-0 top-full z-30 mt-1 w-44 overflow-hidden py-1"
                            >
                                <button type="button" class="fe-view-item {{ $sortBy === 'name' ? 'fe-view-item--active' : '' }}" wire:click="setSort('name')" @click="open=false">
                                    @svg('heroicon-o-document-text', 'h-3.5 w-3.5') Name
                                    @if ($sortBy === 'name')
                                        <span class="ms-auto text-[10px] text-teal-600">{{ $sortDir === 'asc' ? 'A→Z' : 'Z→A' }}</span>
                                    @endif
                                </button>
                                <button type="button" class="fe-view-item {{ $sortBy === 'date' ? 'fe-view-item--active' : '' }}" wire:click="setSort('date')" @click="open=false">
                                    @svg('heroicon-o-calendar', 'h-3.5 w-3.5') Date
                                    @if ($sortBy === 'date')
                                        <span class="ms-auto text-[10px] text-teal-600">{{ $sortDir === 'asc' ? 'Old' : 'New' }}</span>
                                    @endif
                                </button>
                                <button type="button" class="fe-view-item {{ $sortBy === 'type' ? 'fe-view-item--active' : '' }}" wire:click="setSort('type')" @click="open=false">
                                    @svg('heroicon-o-document', 'h-3.5 w-3.5') Type
                                    @if ($sortBy === 'type')
                                        <span class="ms-auto text-[10px] text-teal-600">{{ $sortDir === 'asc' ? 'A→Z' : 'Z→A' }}</span>
                                    @endif
                                </button>
                            </div>
                        </div>

                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" class="fe-tool-btn" title="{{ __('filament-file-explorer::file-explorer.toolbar.view_options') }}" @click="open = !open">
                                @if ($viewMode === 'list')
                                    @svg('heroicon-o-list-bullet', 'h-3.5 w-3.5')
                                @elseif ($viewMode === 'table')
                                    @svg('heroicon-o-table-cells', 'h-3.5 w-3.5')
                                @elseif ($viewMode === 'details')
                                    @svg('heroicon-o-bars-3', 'h-3.5 w-3.5')
                                @else
                                    @svg('heroicon-o-squares-2x2', 'h-3.5 w-3.5')
                                @endif
                            </button>
                            <div
                                x-show="open"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fe-menu absolute end-0 top-full z-30 mt-1 w-44 overflow-hidden py-1"
                            >
                                <button type="button" class="fe-view-item {{ $viewMode === 'grid' ? 'fe-view-item--active' : '' }}" wire:click="setViewMode('grid')" @click="open=false">@svg('heroicon-o-squares-2x2', 'h-3.5 w-3.5') Icons</button>
                                <button type="button" class="fe-view-item {{ $viewMode === 'list' ? 'fe-view-item--active' : '' }}" wire:click="setViewMode('list')" @click="open=false">@svg('heroicon-o-list-bullet', 'h-3.5 w-3.5') List</button>
                                <button type="button" class="fe-view-item {{ $viewMode === 'table' ? 'fe-view-item--active' : '' }}" wire:click="setViewMode('table')" @click="open=false">@svg('heroicon-o-table-cells', 'h-3.5 w-3.5') Columns</button>
                                <button type="button" class="fe-view-item {{ $viewMode === 'details' ? 'fe-view-item--active' : '' }}" wire:click="setViewMode('details')" @click="open=false">@svg('heroicon-o-bars-3', 'h-3.5 w-3.5') Details</button>
                            </div>
                        </div>
                        @if ($abilities['search'])
                        <div class="relative shrink-0">
                            @svg('heroicon-o-magnifying-glass', 'pointer-events-none absolute start-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-zinc-400')
                            <input wire:model.live.debounce.250ms="search" class="fe-input fe-search h-8 pe-3 ps-8 text-xs" type="search" title="{{ __('filament-file-explorer::file-explorer.toolbar.search') }}" placeholder="{{ __('filament-file-explorer::file-explorer.toolbar.search_placeholder') }}">
                        </div>
                        @endif
                    </div>
                </div>

                <div id="filemanager-area" class="fe-browser relative overflow-x-hidden dark:bg-zinc-800/50" x-bind:class="dropingFile ? 'fe-browser--drop' : ''">
                    @if($search)
                        <div class="border-b border-zinc-200 bg-zinc-100/80 px-4 py-1 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 sm:px-5">{{ (count($searchedFiles) + count($folders)) }} {{ trans_choice('filament-file-explorer::file-explorer.search_results', count($searchedFiles) + count($folders)) }}</div>
                    @endif

                    <div
                        id="folder-container"
                        @mousedown.left="initiateDrawing($event)"
                        @if ($abilities['upload'])
                        x-on:drop="dropingFile = false"
                        x-on:drop.prevent="handleFileDrop($event)"
                        x-on:dragover.prevent="dropingFile = true"
                        x-on:dragleave.prevent="dropingFile = false"
                        @endif
                        @if ($abilities['mkdir'])
                        x-on:dblclick.self="$wire.createNewFolder()"
                        @endif
                        x-on:click="handleContainerClick($event)"
                        x-on:contextmenu.prevent="openEmptyContext($event)"
                        @class([
                            'relative min-h-[500px] select-none overflow-y-auto p-2 pb-8',
                            'flex flex-wrap content-start gap-x-0 gap-y-1' => in_array($viewMode, ['grid', 'icons'], true),
                            'space-y-0.5' => in_array($viewMode, ['list', 'table', 'details'], true),
                        ])
                    >
                        <div
                            x-show="drawnArea"
                            x-cloak
                            class="drawn-area absolute z-20"
                            :style="drawnArea ? {
                                left: drawnArea.left + 'px',
                                top: drawnArea.top + 'px',
                                width: drawnArea.width + 'px',
                                height: drawnArea.height + 'px'
                            } : {}"
                        ></div>
                        @if ($isCreatingNewFolder)
                            @if (in_array($viewMode, ['list', 'table', 'details'], true))
                                <div class="flex items-center gap-3 rounded-lg bg-teal-50/70 px-3 py-2 dark:bg-teal-950/20" @click.outside="$wire.cancelNewFolder">
                                    <x-filament-file-explorer::file-explorer.folder-icon class="h-7 w-7 shrink-0" />
                                    <input type="text" id="new-folder-name" wire:model="newFolderName" wire:keydown.enter.prevent="saveNewFolder" wire:keydown.escape.prevent="cancelNewFolder" class="fe-input w-full px-2 py-1 text-sm">
                                </div>
                            @else
                                <div class="fe-icon-item relative z-30 mx-0.5 flex w-[96px] flex-col items-center px-0.5 pt-0.5 pb-0.5 text-center" @click.outside="$wire.cancelNewFolder">
                                    <div class="fe-icon-well fe-icon-well--selected flex h-[68px] w-[76px] items-center justify-center rounded-xl">
                                        <x-filament-file-explorer::file-explorer.folder-icon class="h-[3.35rem] w-[3.35rem]" />
                                    </div>
                                    <input type="text" id="new-folder-name" wire:model="newFolderName" wire:keydown.enter.prevent="saveNewFolder" wire:keydown.escape.prevent="cancelNewFolder" class="fe-input fe-rename-input mt-1.5 w-full px-1 py-0.5 text-center text-[11px]">
                                    @error('newFolderName')
                                        <span class="text-xs text-red-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        @endif

                        @php
                            $isRowView = in_array($viewMode, ['list', 'table', 'details'], true);
                            $cols = match ($viewMode) {
                                'details' => 'grid-cols-[minmax(0,1fr)_6rem_7rem_8rem]',
                                'table' => 'grid-cols-[minmax(0,1fr)_6rem_8rem]',
                                default => 'grid-cols-[minmax(0,1fr)_7rem_8rem]',
                            };
                        @endphp

                        @if ($isRowView)
                            <div class="fe-list-head mb-0 grid {{ $cols }} gap-2 border-b border-zinc-200/80 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wide text-zinc-400 dark:border-zinc-700">
                                <button type="button" class="text-start hover:text-zinc-700 dark:hover:text-zinc-200" wire:click="setSort('name')">Name @if($sortBy==='name'){{ $sortDir==='asc'?'↑':'↓' }}@endif</button>
                                <button type="button" class="text-start hover:text-zinc-700 dark:hover:text-zinc-200" wire:click="setSort('type')">Kind @if($sortBy==='type'){{ $sortDir==='asc'?'↑':'↓' }}@endif</button>
                                @if ($viewMode === 'details')
                                    <span>Size</span>
                                @endif
                                <button type="button" class="text-start hover:text-zinc-700 dark:hover:text-zinc-200" wire:click="setSort('date')">Date Modified @if($sortBy==='date'){{ $sortDir==='asc'?'↑':'↓' }}@endif</button>
                            </div>

                            @php $detailRow = 0; @endphp
                            @foreach($sortedFolders as $folder)
                                @php
                                    $folderRenaming = $renamingType === 'folder' && (int) $renamingId === (int) $folder->id;
                                    $folderSelected = in_array($folder->id, $selectedFolders, false);
                                    $folderItems = (int) ($folder->children_count ?? 0) + (int) ($folder->file_explorer_count ?? 0);
                                    $isStripe = $viewMode === 'details' && ($detailRow % 2 === 1);
                                    $detailRow++;
                                @endphp
                                <div
                                    wire:key="row-folder-{{ $folder->id }}-{{ $viewMode }}"
                                    x-data="{ isDragOver: false }"
                                    data-fe-drop-folder="{{ $folder->id }}"
                                    @unless($folderRenaming)
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
                                        if (!$store.feSel.hasFolder({{ $folder->id }})) { $store.feSel.toggle('folder', {{ $folder->id }}, false); }
                                        $dispatch('fe-context', { type: 'folder', id: {{ $folder->id }}, name: @js($folder->name), x: $event.clientX, y: $event.clientY })
                                    "
                                    @class([
                                        'folder fe-list-row grid cursor-default items-center gap-2 px-3 py-1.5 text-[13px] transition-colors duration-75 ' . $cols,
                                        'rounded-lg border border-zinc-100 dark:border-zinc-700/60' => $viewMode === 'table',
                                        'rounded-none' => $viewMode === 'details',
                                        'fe-list-row--stripe' => $isStripe,
                                    ])
                                    :class="{
                                        'fe-row--selected': ($store.feSel.hasFolder({{ $folder->id }}) || $store.feSel.inMarqueeFolder({{ $folder->id }})) && !@js($folderRenaming),
                                        'drag-hover': $store.feSel.inMarqueeFolder({{ $folder->id }}),
                                        'hover:bg-zinc-100/80 dark:hover:bg-white/5': !$store.feSel.hasFolder({{ $folder->id }}) && !$store.feSel.inMarqueeFolder({{ $folder->id }}),
                                        'ring-2 ring-zinc-300/50 bg-zinc-100/70': isDragOver || $store.feDrag.dropTargetId === {{ $folder->id }},
                                        'fe-dragging': $store.feDrag.active && $store.feSel.hasFolder({{ $folder->id }})
                                    }"
                                    data-id="{{ $folder->id }}"
                                    data-fe-type="folder"
                                >
                                    <div class="flex min-w-0 items-center gap-2">
                                        <x-filament-file-explorer::file-explorer.folder-icon class="h-6 w-6 shrink-0" />
                                        @if ($folderRenaming)
                                            <input type="text" id="rename-input" wire:model="renameValue" wire:keydown.enter.prevent="saveRename" wire:keydown.escape.prevent="cancelRename" wire:blur="saveRename" class="fe-input fe-rename-input w-full px-1.5 py-0.5 text-[13px]" @click.stop @mousedown.stop>
                                        @else
                                            <span class="truncate font-medium text-zinc-800 dark:text-zinc-100">{{ $folder->name }}</span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-zinc-500">Folder</span>
                                    @if ($viewMode === 'details')
                                        <span class="text-xs text-zinc-500">{{ $folderItems }} {{ $folderItems === 1 ? 'item' : 'items' }}</span>
                                    @endif
                                    <span class="text-xs text-zinc-500">{{ $folder->updated_at?->format('Y/m/d H:i') }}</span>
                                </div>
                            @endforeach

                            @foreach($sortedMedia as $media)
                                @php
                                    $fileRenaming = $renamingType === 'file' && (int) $renamingId === (int) $media->id;
                                    $fileSelected = in_array($media->id, $selectedFiles, false);
                                    $fileLabel = \Ardavan\FilamentFileExplorer\Support\MediaLabel::display($media);
                                    $mimeIcon = \Ardavan\FilamentFileExplorer\Support\MimeIcon::forMedia($media);
                                    $listPreview = str_starts_with((string) $media->mime_type, 'image/')
                                        ? ($media->hasGeneratedConversion('thumbnail') ? $media->getUrl('thumbnail') : $this->mediaOpenUrl($media->id))
                                        : null;
                                    $sizeLabel = number_format(((int) $media->size) / 1024, 1) . ' KB';
                                    $isStripe = $viewMode === 'details' && ($detailRow % 2 === 1);
                                    $detailRow++;
                                @endphp
                                <div
                                    wire:key="row-file-{{ $media->id }}-{{ $viewMode }}"
                                    @unless($fileRenaming)
                                        x-on:pointerdown="$store.feDrag.pointerDown($event, 'file', {{ $media->id }}, @js($fileLabel), $wire)"
                                        x-on:click.stop="
                                            if ($store.feDrag.consumeClickSuppression()) return;
                                            $store.feSel.toggle('file', {{ $media->id }}, $event.ctrlKey || $event.metaKey);
                                        "
                                        x-on:dblclick.stop="window.open(@js($this->mediaOpenUrl($media->id)), '_blank')"
                                    @endunless
                                    x-on:contextmenu.stop.prevent="
                                        if (!$store.feSel.hasFile({{ $media->id }})) { $store.feSel.toggle('file', {{ $media->id }}, false); }
                                        $dispatch('fe-context', { type: 'file', id: {{ $media->id }}, name: @js($fileLabel), x: $event.clientX, y: $event.clientY })
                                    "
                                    @class([
                                        'file fe-list-row grid cursor-default items-center gap-2 px-3 py-1.5 text-[13px] transition-colors duration-75 ' . $cols,
                                        'rounded-lg border border-zinc-100 dark:border-zinc-700/60' => $viewMode === 'table',
                                        'rounded-none' => $viewMode === 'details',
                                        'fe-list-row--stripe' => $isStripe,
                                    ])
                                    :class="{
                                        'fe-row--selected': ($store.feSel.hasFile({{ $media->id }}) || $store.feSel.inMarqueeFile({{ $media->id }})) && !@js($fileRenaming),
                                        'drag-hover': $store.feSel.inMarqueeFile({{ $media->id }}),
                                        'hover:bg-zinc-100/80 dark:hover:bg-white/5': !$store.feSel.hasFile({{ $media->id }}) && !$store.feSel.inMarqueeFile({{ $media->id }}),
                                        'fe-dragging': $store.feDrag.active && $store.feSel.hasFile({{ $media->id }})
                                    }"
                                    data-id="{{ $media->id }}"
                                    data-fe-type="file"
                                >
                                    <div class="flex min-w-0 items-center gap-2">
                                        @if ($listPreview)
                                            <img src="{{ $listPreview }}" alt="" draggable="false" loading="lazy" class="pointer-events-none h-5 w-5 object-contain">
                                        @else
                                            <x-filament-file-explorer::file-explorer.mime-icon :icon="$mimeIcon" size="sm" />
                                        @endif
                                        @if ($fileRenaming)
                                            <input type="text" id="rename-input" wire:model="renameValue" wire:keydown.enter.prevent="saveRename" wire:keydown.escape.prevent="cancelRename" wire:blur="saveRename" class="fe-input fe-rename-input w-full px-1.5 py-0.5 text-[13px]" @click.stop @mousedown.stop>
                                        @else
                                            <span class="truncate text-zinc-800 dark:text-zinc-100">{{ $fileLabel }}</span>
                                        @endif
                                    </div>
                                    <span class="truncate text-xs text-zinc-500">{{ strtoupper(pathinfo($media->file_name, PATHINFO_EXTENSION) ?: 'FILE') }}</span>
                                    @if ($viewMode === 'details')
                                        <span class="text-xs text-zinc-500">{{ $sizeLabel }}</span>
                                    @endif
                                    <span class="text-xs text-zinc-500">{{ $media->created_at?->format('Y/m/d H:i') }}</span>
                                </div>
                            @endforeach
                        @else
                            @foreach($sortedFolders as $folder)
                                <x-filament-file-explorer::file-explorer.directory
                                    :folder="$folder"
                                    :selectedFolders="$selectedFolders"
                                    :selectedFiles="$selectedFiles"
                                    :renamingType="$renamingType"
                                    :renamingId="$renamingId"
                                    wire:key="folder-{{ $folder->id }}"
                                />
                            @endforeach

                            @foreach($sortedMedia as $media)
                                <x-filament-file-explorer::file-explorer.media
                                    :media="$media"
                                    :selectedFiles="$selectedFiles"
                                    :selectedFolders="$selectedFolders"
                                    :openUrl="$this->mediaOpenUrl($media->id)"
                                    :previewUrl="$this->mediaOpenUrl($media->id)"
                                    :renamingType="$renamingType"
                                    :renamingId="$renamingId"
                                    :key="'file-' . $media->id"
                                    wire:key="file-{{ $media->id }}"
                                />
                            @endforeach
                        @endif
                    </div>

                    <div class="pointer-events-none absolute inset-x-3 bottom-3 z-20" x-cloak x-show="$store.feUpload.visible"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="overflow-hidden rounded-lg border bg-white/95 px-3 py-2 shadow-lg backdrop-blur dark:bg-zinc-900/95"
                             :class="$store.feUpload.status === 'error' || $store.feUpload.status === 'cancelled'
                                ? 'border-red-200 dark:border-red-800'
                                : ($store.feUpload.status === 'done' ? 'border-emerald-200 dark:border-emerald-800' : 'border-zinc-200 dark:border-zinc-700')">
                            <div class="mb-1.5 flex items-center justify-between gap-2 text-[11px]"
                                 :class="$store.feUpload.status === 'error' || $store.feUpload.status === 'cancelled' ? 'text-red-600 dark:text-red-400' : ($store.feUpload.status === 'done' ? 'text-emerald-600' : 'text-zinc-500')">
                                <span x-text="$store.feUpload.label"></span>
                                <span x-show="$store.feUpload.status === 'uploading'" x-text="Math.round($store.feUpload.progress) + '%'"></span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div class="h-full rounded-full transition-all duration-200"
                                     :class="$store.feUpload.status === 'error' || $store.feUpload.status === 'cancelled' ? 'bg-red-500' : ($store.feUpload.status === 'done' ? 'bg-emerald-500' : 'bg-sky-500')"
                                     :style="'width:' + ($store.feUpload.status === 'error' || $store.feUpload.status === 'cancelled' ? 100 : $store.feUpload.progress) + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="flex select-none items-center gap-0.5 border-t border-zinc-100 px-2 py-1.5 text-xs dark:border-zinc-700/80 dark:text-zinc-300">
                    @foreach ($breadcrumb as $index => $folder)
                        <span
                            class="flex cursor-default items-center gap-x-1 rounded-lg px-2 py-1 transition-colors hover:bg-zinc-100 dark:hover:bg-white/10"
                            @unless($loop->last)
                                data-fe-drop-folder="{{ $folder->id }}"
                            @endunless
                            :class="{ 'fe-side-item--drop bg-zinc-100 dark:bg-white/10': $store.feDrag.dropTargetId === {{ (int) $folder->id }} && {{ $loop->last ? 'false' : 'true' }} }"
                            wire:click.prevent="navigateToBreadcrumb({{ $index }})"
                        >
                            <x-filament-file-explorer::file-explorer.folder-icon class="h-3.5 w-3.5" /> <span>{{ $folder->name }}</span>
                        </span>
                        @if (!$loop->last)
                            @svg('heroicon-o-chevron-left', 'h-3 w-3 shrink-0 rotate-180 text-zinc-300 rtl:rotate-0')
                        @endif
                    @endforeach
                </nav>
                    </div>{{-- end main column --}}

                    {{-- Get Info inspector --}}
                    @if ($showInfoModal && $infoItem)
                        <aside class="fe-inspector flex w-[300px] shrink-0 flex-col"
                               dir="ltr"
                               lang="en"
                               translate="no"
                               wire:key="info-en-{{ $infoItem['type'] ?? 'x' }}-{{ $infoItem['id'] ?? 0 }}"
                               x-data x-init="$el.animate([{opacity:0,transform:'translateX(8px)'},{opacity:1,transform:'translateX(0)'}],{duration:160,easing:'cubic-bezier(.2,.8,.2,1)'})">
                            <div class="fe-inspector-head flex items-center justify-between gap-2 px-3 py-2.5">
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500" translate="no">Get Info</span>
                                <button type="button" class="fe-tool-btn" wire:click="closeInfo" title="Close">
                                    @svg('heroicon-o-x-mark', 'h-3.5 w-3.5')
                                </button>
                            </div>
                            <div class="flex flex-1 flex-col gap-3 overflow-y-auto p-3">
                                <div class="fe-inspector-hero flex flex-col items-center px-2 py-3 text-center">
                                    @if (($infoItem['type'] ?? '') === 'folder' || ($infoItem['type'] ?? '') === 'multi')
                                        <x-filament-file-explorer::file-explorer.folder-icon class="mb-2 h-16 w-16" />
                                    @elseif (!empty($infoItem['preview']))
                                        <img src="{{ $infoItem['preview'] }}" alt="" class="mb-2 h-20 w-20 rounded-lg object-cover ring-1 ring-black/5">
                                    @else
                                        <x-filament-file-explorer::file-explorer.mime-icon :icon="$infoItem['icon'] ?? 'file'" size="lg" class="mb-2" />
                                    @endif
                                    <div class="w-full truncate text-[13px] font-semibold text-zinc-900 dark:text-zinc-100">{{ $infoItem['name'] }}</div>
                                    <div class="mt-0.5 text-[11px] text-zinc-400">{{ $infoItem['mime'] }}</div>
                                </div>
                                <dl class="fe-inspector-meta" translate="no">
                                    <div class="fe-inspector-row"><dt>Size</dt><dd>{{ $infoItem['size'] }}</dd></div>
                                    <div class="fe-inspector-row"><dt>Where</dt><dd class="break-all text-end">{{ $infoItem['path'] }}</dd></div>
                                    <div class="fe-inspector-row"><dt>Permissions</dt><dd class="text-end">{{ $infoItem['permissions'] }}</dd></div>
                                    @if (!empty($infoItem['delete_note']))
                                        <div class="fe-inspector-row"><dt>Delete</dt><dd class="text-end text-amber-700 dark:text-amber-300">{{ $infoItem['delete_note'] }}</dd></div>
                                    @endif
                                    <div class="fe-inspector-row"><dt>Created</dt><dd>{{ $infoItem['created'] }}</dd></div>
                                    <div class="fe-inspector-row"><dt>Modified</dt><dd>{{ $infoItem['updated'] }}</dd></div>
                                    @if (!empty($infoItem['extra']))
                                        <div class="fe-inspector-row"><dt>Details</dt><dd class="break-all text-end">{{ $infoItem['extra'] }}</dd></div>
                                    @endif
                                </dl>
                            </div>
                        </aside>
                    @endif
                </div>{{-- end flex row --}}
            </div>

            {{-- Context menu --}}
            <div
                x-show="ctx.open"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fe-context-menu fixed z-[100] min-w-[220px] origin-top-left overflow-visible py-1"
                dir="ltr"
                :style="{ left: ctx.x + 'px', top: ctx.y + 'px' }"
                @click.stop
                @contextmenu.prevent
            >
                <template x-if="ctx.type === 'empty'">
                    <div>
                        <template x-if="abilities.mkdir">
                            <button type="button" class="fe-ctx-item" @click="run(() => $wire.createNewFolder())">
                                @svg('heroicon-o-folder-plus', 'fe-ctx-icon') New Folder
                            </button>
                        </template>
                        <template x-if="abilities.upload">
                            <button type="button" class="fe-ctx-item" @click="run(() => document.getElementById('fileInput')?.click())">
                                @svg('heroicon-o-arrow-up-tray', 'fe-ctx-icon') Upload Files
                            </button>
                        </template>
                        <template x-if="abilities.move || abilities.copy">
                            <div>
                                <div class="fe-ctx-sep" x-show="abilities.mkdir || abilities.upload"></div>
                                <button type="button" class="fe-ctx-item" :disabled="!$wire.clipboardReady" @click="run(() => $wire.pasteClipboard())">
                                    @svg('heroicon-o-clipboard-document', 'fe-ctx-icon') Paste
                                </button>
                            </div>
                        </template>
                        <template x-if="abilities.getInfo">
                            <div>
                                <div class="fe-ctx-sep"></div>
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.showInfo())">
                                    @svg('heroicon-o-information-circle', 'fe-ctx-icon') Get Info
                                </button>
                            </div>
                        </template>
                        <div class="fe-ctx-sep"></div>

                        <div
                            class="fe-ctx-flyout"
                            x-data="qfCtxFlyout()"
                            @mouseenter="show()"
                            @mouseleave="hide()"
                        >
                            <button type="button" class="fe-ctx-item fe-ctx-item--fly" tabindex="-1" @click.prevent>
                                <span class="inline-flex items-center gap-2">@svg('heroicon-o-squares-2x2', 'fe-ctx-icon') View</span>
                                @svg('heroicon-o-chevron-right', 'h-3.5 w-3.5 opacity-40')
                            </button>
                            <div class="fe-ctx-sub" x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setViewMode('grid'))">@svg('heroicon-o-squares-2x2', 'fe-ctx-icon') Icons</button>
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setViewMode('list'))">@svg('heroicon-o-list-bullet', 'fe-ctx-icon') List</button>
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setViewMode('table'))">@svg('heroicon-o-table-cells', 'fe-ctx-icon') Columns</button>
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setViewMode('details'))">@svg('heroicon-o-bars-3', 'fe-ctx-icon') Details</button>
                            </div>
                        </div>

                        <div
                            class="fe-ctx-flyout"
                            x-data="qfCtxFlyout()"
                            @mouseenter="show()"
                            @mouseleave="hide()"
                        >
                            <button type="button" class="fe-ctx-item fe-ctx-item--fly" tabindex="-1" @click.prevent>
                                <span class="inline-flex items-center gap-2">@svg('heroicon-o-arrows-up-down', 'fe-ctx-icon') Sort By</span>
                                @svg('heroicon-o-chevron-right', 'h-3.5 w-3.5 opacity-40')
                            </button>
                            <div class="fe-ctx-sub" x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0">
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setSort('name'))">Name</button>
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setSort('date'))">Date</button>
                                <button type="button" class="fe-ctx-item" @click="run(() => $wire.setSort('type'))">Type</button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="ctx.type === 'folder' || ctx.type === 'file'">
                    <div>
                        <button type="button" class="fe-ctx-item" x-show="ctx.type === 'folder'" @click="run(() => $wire.openFolder(ctx.id))">
                            @svg('heroicon-o-folder-open', 'fe-ctx-icon') Open
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="ctx.type === 'folder'" @click="run(() => { const u = new URL(window.location.href); u.searchParams.set('folder', ctx.id); window.open(u.toString(), '_blank'); })">
                            @svg('heroicon-o-arrow-top-right-on-square', 'fe-ctx-icon') Open in New Tab
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="ctx.type === 'file'" @click="run(() => window.open(fileUrl(ctx.id, false), '_self'))">
                            @svg('heroicon-o-eye', 'fe-ctx-icon') Open
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="ctx.type === 'file'" @click="run(() => window.open(fileUrl(ctx.id, false), '_blank'))">
                            @svg('heroicon-o-arrow-top-right-on-square', 'fe-ctx-icon') Open in New Tab
                        </button>
                        <div class="fe-ctx-sep" x-show="abilities.rename || abilities.copy || abilities.move"></div>
                        <button type="button" class="fe-ctx-item" x-show="abilities.rename" @click="run(() => $wire.startRename(ctx.type, ctx.id))">
                            @svg('heroicon-o-pencil', 'fe-ctx-icon') Rename
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="abilities.copy" @click="run(() => $wire.copySelection(ctx.type === 'folder' ? ctx.id : null, ctx.type === 'file' ? ctx.id : null))">
                            @svg('heroicon-o-document-duplicate', 'fe-ctx-icon') Copy
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="abilities.move" @click="run(() => $wire.cutSelection(ctx.type === 'folder' ? ctx.id : null, ctx.type === 'file' ? ctx.id : null))">
                            @svg('heroicon-o-scissors', 'fe-ctx-icon') Cut
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="abilities.move || abilities.copy" :disabled="!$wire.clipboardReady" @click="run(() => $wire.pasteClipboard())">
                            @svg('heroicon-o-clipboard-document', 'fe-ctx-icon') Paste
                        </button>
                        <div class="fe-ctx-sep" x-show="abilities.download"></div>
                        <button type="button" class="fe-ctx-item" x-show="abilities.download && ctx.type === 'file'" @click="run(() => window.location.href = fileUrl(ctx.id, true))">
                            @svg('heroicon-o-arrow-down-tray', 'fe-ctx-icon') Download
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="abilities.download && ctx.type === 'folder'" @click="run(() => window.location.href = folderZipUrl(ctx.id))">
                            @svg('heroicon-o-arrow-down-tray', 'fe-ctx-icon') Download ZIP
                        </button>
                        <button type="button" class="fe-ctx-item" x-show="abilities.download" @click="run(() => window.location.href = ctx.type === 'folder' ? folderZipUrl(ctx.id) : mediaZipUrl(ctx.id))">
                            @svg('heroicon-o-archive-box', 'fe-ctx-icon') Compress to ZIP
                        </button>
                        <div class="fe-ctx-sep" x-show="abilities.getInfo"></div>
                        <button type="button" class="fe-ctx-item" x-show="abilities.getInfo" @click="run(() => $wire.showInfo(ctx.type, ctx.id))">
                            @svg('heroicon-o-information-circle', 'fe-ctx-icon') Get Info
                        </button>
                        <div class="fe-ctx-sep" x-show="(abilities.delete && ctx.type === 'file') || (abilities.deleteFolder && ctx.type === 'folder')"></div>
                        <button type="button" class="fe-ctx-item fe-ctx-danger"
                            x-show="(abilities.delete && ctx.type === 'file') || (abilities.deleteFolder && ctx.type === 'folder')"
                            :disabled="!ctx.canDelete || (ctx.type === 'folder' && ctx.id === rootFolderId && ($store.feSel.folders.length + $store.feSel.files.length) <= 1)"
                            :title="ctx.deleteHint || ''"
                            @click="run(() => {
                                if (!ctx.canDelete) {
                                    alert(ctx.deleteHint || (translations?.js?.delete_not_allowed ?? @js(__('filament-file-explorer::file-explorer.js.delete_not_allowed'))));
                                    return;
                                }
                                confirmDeleteSelected();
                            })">
                            @svg('heroicon-o-trash', 'fe-ctx-icon') Delete
                        </button>
                        <div
                            x-show="((abilities.delete && ctx.type === 'file') || (abilities.deleteFolder && ctx.type === 'folder')) && ctx.deleteHint"
                            class="px-3 pb-1.5 text-[10px] leading-snug text-zinc-500 dark:text-zinc-400"
                            x-text="ctx.deleteHint"
                        ></div>
                    </div>
                </template>
            </div>
        </div>
    @endif
</div>
