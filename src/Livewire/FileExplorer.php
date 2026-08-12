<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Livewire;


use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer;

use Ardavan\FilamentFileExplorer\Support\MediaLabel;
use Ardavan\FilamentFileExplorer\Support\MimeIcon;
use Ardavan\FilamentFileExplorer\Support\UploadRules;
use Filament\Notifications\Notification;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Ardavan\FilamentFileExplorer\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FileExplorer extends \Livewire\Component
{
    use WithFileUploads;

    public string $scopeKey = 'default';

    public ?Folder $currentFolder = null;

    /** @var list<Folder> */
    public array $breadcrumb = [];

    /** @var \Illuminate\Support\Collection<int, Folder>|array */
    public $folders = [];

    public $searchedFiles = null;

    /** @var list<int> */
    public array $selectedFolders = [];

    /** @var list<int> */
    public array $selectedFiles = [];

    public string $search = '';

    /** @var array<int, TemporaryUploadedFile|null> */
    public array $files = [];

    public bool $isCreatingNewFolder = false;

    public string $newFolderName = '';

    public int $rootFolderId = 0;

    public string $viewMode = 'grid';

    public string $sortBy = 'name';

    public string $sortDir = 'asc';

    public ?string $renamingType = null;

    public ?int $renamingId = null;

    public string $renameValue = '';

    /** @var array{type:?string,id:?int,name:?string,size:?string,path:?string,mime:?string,permissions:?string,created:?string,updated:?string,extra:?string,preview:?string,icon?:string}|null */
    public ?array $infoItem = null;

    public bool $showInfoModal = false;

    public bool $clipboardReady = false;

    public ?int $uploadTargetFolderId = null;

    public int $fileInputKey = 0;

    /** @var list<int> */
    public array $navHistory = [];

    public int $navIndex = -1;

    public function mount(?string $scopeKey = null, ?int $rootFolderId = null): void
    {
        $this->scopeKey = (string) ($scopeKey ?? $this->scopeKey);
        $this->rootFolderId = (int) ($rootFolderId ?? $this->rootFolderId);

        $authorizer = app(FileExplorerAuthorizer::class);

        abort_unless($authorizer->canAccess($this->scopeKey, $this->rootFolderId), 403);

        $this->clipboardReady = $this->hasClipboard();

        $sessionKey = $this->sessionKey();
        $requestedFolder = request()->integer('folder') ?: null;
        $currentId = $requestedFolder ?: session($sessionKey, $this->rootFolderId);

        $folder = Folder::with(['children', 'parent'])->find($currentId);

        if (! $folder || ! app(FolderTree::class)->isUnderRoot($folder, $this->rootFolderId)) {
            $folder = Folder::with(['children', 'parent'])->findOrFail($this->rootFolderId);
            session([$sessionKey => $folder->id]);
        }

        $this->currentFolder = $folder;
        $this->breadcrumb = $this->generateBreadcrumb($this->currentFolder);
        $this->navHistory = [(int) $folder->id];
        $this->navIndex = 0;
        $this->loadFolders();
    }

    protected function sessionKey(): string
    {
        return 'currentFolderId.'.$this->scopeKey;
    }

    protected function clipboardKey(): string
    {
        return 'clipboard.'.$this->scopeKey;
    }


    protected function assertUnderRoot(?Folder $folder): void
    {
        abort_unless(
            $folder && app(FolderTree::class)->isUnderRoot($folder, $this->rootFolderId),
            403
        );
    }

    public function setViewMode(string $mode): void
    {
        if ($mode === 'icons') {
            $mode = 'grid';
        }

        $this->viewMode = in_array($mode, ['grid', 'list', 'table', 'details'], true) ? $mode : 'grid';
    }

    public function acceptAttribute(): string
    {
        return implode(',', UploadRules::acceptedFileTypes());
    }

    /**
     * @return array{
     *     browse: bool,
     *     search: bool,
     *     getInfo: bool,
     *     download: bool,
     *     upload: bool,
     *     mkdir: bool,
     *     rename: bool,
     *     move: bool,
     *     copy: bool,
     *     delete: bool,
     *     deleteFolder: bool
     * }
     */
    public function abilities(): array
    {
        return app(FileExplorerAuthorizer::class)->abilities($this->scopeKey, $this->rootFolderId);
    }

    protected function authorizer(): FileExplorerAuthorizer
    {
        return app(FileExplorerAuthorizer::class);
    }

    protected function ability(string $key): bool
    {
        return (bool) ($this->abilities()[$key] ?? false);
    }

    protected function rules(): array
    {
        return [
            'files.*' => [
                'file',
                'max:'.UploadRules::maxSizeKb(),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof \Illuminate\Http\UploadedFile) {
                        $fail(__('filament-file-explorer::file-explorer.validation.invalid_file'));

                        return;
                    }

                    if (! UploadRules::isAllowedUpload($value)) {
                        $fail(__('filament-file-explorer::file-explorer.validation.invalid_format'));
                    }
                },
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'files.*.file' => __('filament-file-explorer::file-explorer.validation.invalid_file'),
            'files.*.max' => __('filament-file-explorer::file-explorer.validation.file_too_large', ['max' => UploadRules::maxSizeKb()]),
        ];
    }

    public function updatedSearch(): void
    {
        abort_unless($this->ability('search'), 403);

        $this->selectedFolders = [];
        $this->selectedFiles = [];
        $this->dispatch('fe-sel-cleared');
        $this->loadFolders();
    }

    public function canGoBack(): bool
    {
        return $this->navIndex > 0;
    }

    public function canGoForward(): bool
    {
        return $this->navIndex >= 0 && $this->navIndex < count($this->navHistory) - 1;
    }

    public function goBack(): void
    {
        if (! $this->canGoBack()) {
            return;
        }

        $this->navIndex--;
        $this->openFolderFromHistory((int) $this->navHistory[$this->navIndex]);
    }

    public function goForward(): void
    {
        if (! $this->canGoForward()) {
            return;
        }

        $this->navIndex++;
        $this->openFolderFromHistory((int) $this->navHistory[$this->navIndex]);
    }

    protected function pushNavHistory(int $folderId): void
    {
        if (($this->navHistory[$this->navIndex] ?? null) === $folderId) {
            return;
        }

        if ($this->navIndex < count($this->navHistory) - 1) {
            $this->navHistory = array_values(array_slice($this->navHistory, 0, $this->navIndex + 1));
        }

        $this->navHistory[] = $folderId;
        $this->navIndex = count($this->navHistory) - 1;
    }

    protected function openFolderFromHistory(int $folderId): void
    {
        $folder = Folder::query()->findOrFail($folderId);
        $this->assertUnderRoot($folder);

        $this->search = '';
        $this->selectedFolders = [];
        $this->selectedFiles = [];
        $this->dispatch('reset-folder');
        $this->dispatch('fe-sel-cleared');

        $this->currentFolder = $folder;
        $this->breadcrumb = $this->generateBreadcrumb($this->currentFolder);
        $this->loadFolders();
        session([$this->sessionKey() => $folder->id]);
    }

    public function setSort(string $by, ?string $dir = null): void
    {
        if (! in_array($by, ['name', 'date', 'type'], true)) {
            return;
        }

        if ($dir === null) {
            if ($this->sortBy === $by) {
                $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortBy = $by;
                $this->sortDir = 'asc';
            }

            return;
        }

        $this->sortBy = $by;
        $this->sortDir = $dir === 'desc' ? 'desc' : 'asc';
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Folder>  $folders
     * @return \Illuminate\Support\Collection<int, Folder>
     */
    public function sortedFolders($folders)
    {
        $dir = $this->sortDir === 'desc';

        return match ($this->sortBy) {
            'date' => $dir ? $folders->sortByDesc('updated_at') : $folders->sortBy('updated_at'),
            'type' => $dir ? $folders->sortByDesc('name') : $folders->sortBy('name'),
            default => $dir ? $folders->sortByDesc('name') : $folders->sortBy('name'),
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Media>  $files
     * @return \Illuminate\Support\Collection<int, Media>
     */
    public function sortedFiles($files)
    {
        $dir = $this->sortDir === 'desc';

        return match ($this->sortBy) {
            'date' => $dir ? $files->sortByDesc('created_at') : $files->sortBy('created_at'),
            'type' => $dir
                ? $files->sortByDesc(fn (Media $m) => strtolower(pathinfo($m->file_name, PATHINFO_EXTENSION)))
                : $files->sortBy(fn (Media $m) => strtolower(pathinfo($m->file_name, PATHINFO_EXTENSION))),
            default => $dir
                ? $files->sortByDesc(fn (Media $m) => strtolower($m->name ?: $m->file_name))
                : $files->sortBy(fn (Media $m) => strtolower($m->name ?: $m->file_name)),
        };
    }

    public function handleMediaClick($fileId): void
    {
        // Selection only — no detail panel.
    }

    public function handleFolderClick($folderId): void
    {
        // Selection only — no detail panel.
    }

    public function setSelection($folders, $files): void
    {
        $this->selectedFolders = array_values(array_map('intval', (array) $folders));
        $this->selectedFiles = array_values(array_map('intval', (array) $files));
    }

    public function clearSelection(): void
    {
        $this->selectedFolders = [];
        $this->selectedFiles = [];
    }

    public function hasClipboard(): bool
    {
        $clip = session($this->clipboardKey());

        return is_array($clip)
            && (($clip['folders'] ?? []) !== [] || ($clip['files'] ?? []) !== []);
    }

    public function clipboardMode(): ?string
    {
        $clip = session($this->clipboardKey());

        return is_array($clip) ? ($clip['mode'] ?? null) : null;
    }

    public function copySelection(?int $folderId = null, ?int $fileId = null): void
    {
        abort_unless($this->ability('copy'), 403);
        $this->writeClipboard('copy', $folderId, $fileId);
        Notification::make()->success()->title(__('filament-file-explorer::file-explorer.copied'))->send();
    }

    public function cutSelection(?int $folderId = null, ?int $fileId = null): void
    {
        abort_unless($this->ability('move'), 403);
        $this->writeClipboard('cut', $folderId, $fileId);
        Notification::make()->success()->title(__('filament-file-explorer::file-explorer.cut'))->send();
    }

    protected function writeClipboard(string $mode, ?int $folderId, ?int $fileId): void
    {
        $folders = $folderId !== null ? [$folderId] : $this->selectedFolders;
        $files = $fileId !== null ? [$fileId] : $this->selectedFiles;

        if ($folderId !== null) {
            $files = [];
        }
        if ($fileId !== null) {
            $folders = [];
        }

        $folders = array_values(array_filter($folders, fn ($id) => (int) $id !== $this->rootFolderId));

        session([$this->clipboardKey() => [
            'mode'    => $mode,
            'folders' => array_map('intval', $folders),
            'files'   => array_map('intval', $files),
        ]]);
        $this->clipboardReady = $this->hasClipboard();
    }

    public function pasteClipboard(): void
    {
        $docs = app(FileExplorerAuthorizer::class);
        $this->assertUnderRoot($this->currentFolder);

        $clip = session($this->clipboardKey());
        if (! is_array($clip)) {
            return;
        }

        $mode = $clip['mode'] ?? 'copy';
        $folderIds = $clip['folders'] ?? [];
        $fileIds = $clip['files'] ?? [];

        if ($mode === 'cut') {
            abort_unless($this->ability('move'), 403);
            $this->moveItemsToFolder($this->currentFolder->id, $folderIds, $fileIds);
            session()->forget($this->clipboardKey());
            $this->clipboardReady = false;
            Notification::make()->success()->title(__('filament-file-explorer::file-explorer.pasted'))->send();

            return;
        }

        abort_unless($this->ability('copy'), 403);

        foreach ($folderIds as $folderId) {
            $source = Folder::query()->find($folderId);
            if (! $source || (int) $source->id === $this->rootFolderId) {
                continue;
            }
            $this->assertUnderRoot($source);
            $this->copyFolderRecursive($source, $this->currentFolder);
        }

        foreach ($fileIds as $fileId) {
            $media = Media::query()->find($fileId);
            if (! $media) {
                continue;
            }
            $source = Folder::query()->find($media->model_id);
            if ($source) {
                $this->assertUnderRoot($source);
            }
            $this->duplicateMedia($media, $this->currentFolder);
        }

        $this->currentFolder = $this->currentFolder->fresh(['children']);
        $this->loadFolders();
        Notification::make()->success()->title(__('filament-file-explorer::file-explorer.pasted'))->send();
    }

    protected function duplicateMedia(Media $media, Folder $target): void
    {
        $path = $media->getPath();
        if (! is_file($path)) {
            return;
        }

        $actor = auth()->user();
        $target
            ->addMedia($path)
            ->preservingOriginal()
            ->usingName($media->name)
            ->usingFileName($media->file_name)
            ->withCustomProperties(array_merge($media->custom_properties ?? [], [
                'user_id'          => $actor?->getAuthIdentifier(),
                'uploaded_by_type' => $actor ? $actor::class : null,
                'uploaded_by_id'   => $actor?->getAuthIdentifier(),
            ]))
            ->toMediaCollection(UploadRules::collection());
    }

    protected function copyFolderRecursive(Folder $source, Folder $parent): Folder
    {
        $name = $source->name;
        $slug = Str::slug($name) ?: ('folder-'.Str::lower(Str::random(6)));
        $baseSlug = $slug;
        $i = 1;
        while (Folder::query()->where('parent_id', $parent->id)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i;
            $name = $source->name.' ('.$i.')';
            $i++;
        }

        $folder = new Folder;
        $folder->name = $name;
        $folder->slug = $slug;
        $folder->parent_id = $parent->id;
        $folder->save();

        foreach ($source->getMedia(UploadRules::collection()) as $media) {
            $this->duplicateMedia($media, $folder);
        }

        foreach ($source->children as $child) {
            $this->copyFolderRecursive($child, $folder);
        }

        return $folder;
    }

    public function startRename(?string $type = null, ?int $id = null): void
    {
        abort_unless($this->ability('rename'), 403);

        if ($type === 'folder' && $id) {
            if ((int) $id === $this->rootFolderId) {
                Notification::make()->warning()->title(__('filament-file-explorer::file-explorer.root_not_renamable'))->send();

                return;
            }
            $folder = Folder::query()->findOrFail($id);
            $this->assertUnderRoot($folder);
            $this->renamingType = 'folder';
            $this->renamingId = $id;
            $this->renameValue = $folder->name;
            $this->dispatch('focus-rename-input');

            return;
        }

        if ($type === 'file' && $id) {
            $media = Media::query()->findOrFail($id);
            $folder = Folder::query()->find($media->model_id);
            if ($folder) {
                $this->assertUnderRoot($folder);
            }
            $this->renamingType = 'file';
            $this->renamingId = $id;
            $this->renameValue = $media->name ?: pathinfo($media->file_name, PATHINFO_FILENAME);
            $this->dispatch('focus-rename-input');

            return;
        }

        if (count($this->selectedFolders) === 1 && count($this->selectedFiles) === 0) {
            $this->startRename('folder', (int) $this->selectedFolders[0]);
        } elseif (count($this->selectedFiles) === 1 && count($this->selectedFolders) === 0) {
            $this->startRename('file', (int) $this->selectedFiles[0]);
        }
    }

    public function cancelRename(): void
    {
        $this->renamingType = null;
        $this->renamingId = null;
        $this->renameValue = '';
    }

    public function saveRename(): void
    {
        abort_unless($this->ability('rename'), 403);

        $name = trim($this->renameValue);
        if ($name === '' || ! $this->renamingType || ! $this->renamingId) {
            $this->cancelRename();

            return;
        }

        if ($this->renamingType === 'folder') {
            if ((int) $this->renamingId === $this->rootFolderId) {
                $this->cancelRename();

                return;
            }
            $folder = Folder::query()->findOrFail($this->renamingId);
            $this->assertUnderRoot($folder);
            $slug = Str::slug($name) ?: ('folder-'.Str::lower(Str::random(6)));
            $exists = Folder::query()
                ->where('parent_id', $folder->parent_id)
                ->where('slug', $slug)
                ->where('id', '!=', $folder->id)
                ->exists();
            if ($exists) {
                Notification::make()->danger()->title(__('filament-file-explorer::file-explorer.duplicate_folder'))->send();

                return;
            }
            $folder->name = $name;
            $folder->slug = $slug;
            $folder->save();
        } else {
            $media = Media::query()->findOrFail($this->renamingId);
            $folder = Folder::query()->find($media->model_id);
            if ($folder) {
                $this->assertUnderRoot($folder);
            }
            $media->name = $name;
            $media->save();
        }

        $this->cancelRename();
        $this->currentFolder = $this->currentFolder->fresh(['children']);
        $this->loadFolders();
    }

    public function deleteTarget(?string $type = null, ?int $id = null): void
    {
        if ($type === 'folder' && $id) {
            $this->selectedFolders = [$id];
            $this->selectedFiles = [];
        } elseif ($type === 'file' && $id) {
            $this->selectedFiles = [$id];
            $this->selectedFolders = [];
        }

        $this->deleteItems();
    }

    public function openFolder(int $folderId): void
    {
        $this->navigateToFolder($folderId);
    }

    public function selectFolder(int $folderId, bool $multi = false): void
    {
        if (! $multi) {
            $this->selectedFolders = [$folderId];
            $this->selectedFiles = [];

            return;
        }

        if (in_array($folderId, $this->selectedFolders, true) || in_array($folderId, $this->selectedFolders)) {
            $this->selectedFolders = array_values(array_filter(
                $this->selectedFolders,
                fn ($id) => (int) $id !== $folderId
            ));

            return;
        }

        $this->selectedFolders[] = $folderId;
    }

    public function selectFile(int $fileId, bool $multi = false): void
    {
        if (! $multi) {
            $this->selectedFiles = [$fileId];
            $this->selectedFolders = [];

            return;
        }

        if (in_array($fileId, $this->selectedFiles, true) || in_array($fileId, $this->selectedFiles)) {
            $this->selectedFiles = array_values(array_filter(
                $this->selectedFiles,
                fn ($id) => (int) $id !== $fileId
            ));

            return;
        }

        $this->selectedFiles[] = $fileId;
    }

    public function showInfo(?string $type = null, ?int $id = null): void
    {
        abort_unless($this->ability('getInfo'), 403);

        if ($type === null && $id === null) {
            if (count($this->selectedFolders) === 1 && count($this->selectedFiles) === 0) {
                $this->showInfo('folder', (int) $this->selectedFolders[0]);

                return;
            }

            if (count($this->selectedFiles) === 1 && count($this->selectedFolders) === 0) {
                $this->showInfo('file', (int) $this->selectedFiles[0]);

                return;
            }

            if ((count($this->selectedFolders) + count($this->selectedFiles)) > 1) {
                $count = count($this->selectedFolders) + count($this->selectedFiles);
                $this->infoItem = [
                    'type'        => 'multi',
                    'id'          => null,
                    'name'        => $count.' items selected',
                    'size'        => '—',
                    'path'        => $this->folderPathString($this->currentFolder),
                    'mime'        => count($this->selectedFolders).' folders · '.count($this->selectedFiles).' files',
                    'permissions' => '—',
                    'created'     => '—',
                    'updated'     => '—',
                    'extra'       => null,
                ];
                $this->showInfoModal = true;

                return;
            }
        }

        if ($type === 'folder' && $id) {
            $folder = Folder::query()->findOrFail($id);
            $this->assertUnderRoot($folder);
            $size = $this->folderSizeBytes($folder);
            $items = (int) $folder->children()->count() + $folder->getMedia(UploadRules::collection())->count();
            $this->infoItem = [
                'type'        => 'folder',
                'id'          => $folder->id,
                'name'        => $folder->name,
                'size'        => $this->formatBytes($size),
                'path'        => $this->folderPathString($folder),
                'mime'        => 'Folder',
                'permissions' => $this->folderPermissionLabel($folder),
                'created'     => $folder->created_at?->format('Y/m/d H:i'),
                'updated'     => $folder->updated_at?->format('Y/m/d H:i'),
                'extra'       => $items.' '.($items === 1 ? 'item' : 'items'),
                'delete_note' => $this->folderDeleteNote($folder),
            ];
            $this->showInfoModal = true;

            return;
        }

        if ($type === 'file' && $id) {
            $media = Media::query()->findOrFail($id);
            $folder = Folder::query()->find($media->model_id);
            if ($folder) {
                $this->assertUnderRoot($folder);
            }
            $deleteState = app(FileExplorerAuthorizer::class)->mediaDeleteState($this->scopeKey, $media);
            $this->infoItem = [
                'type'        => 'file',
                'id'          => $media->id,
                'name'        => MediaLabel::display($media),
                'size'        => $this->formatBytes((int) $media->size),
                'path'        => ($folder ? $this->folderPathString($folder).' / ' : '').$media->file_name,
                'mime'        => $media->mime_type ?: '—',
                'permissions' => $this->mediaPermissionLabel($media),
                'created'     => $media->created_at?->format('Y/m/d H:i'),
                'updated'     => $media->updated_at?->format('Y/m/d H:i'),
                'extra'       => $media->file_name,
                'icon'        => MimeIcon::forMedia($media),
                'preview'     => str_starts_with((string) $media->mime_type, 'image/')
                    ? $this->mediaOpenUrl($media->id)
                    : null,
                'delete_note' => $deleteState['reason'],
            ];
            $this->showInfoModal = true;

            return;
        }

        // Empty space / current folder
        $folder = $this->currentFolder;
        $this->assertUnderRoot($folder);
        $size = $this->folderSizeBytes($folder);
        $items = (int) $folder->children()->count() + $folder->getMedia(UploadRules::collection())->count();
        $this->infoItem = [
            'type'        => 'folder',
            'id'          => $folder->id,
            'name'        => $folder->name,
            'size'        => $this->formatBytes($size),
            'path'        => $this->folderPathString($folder),
            'mime'        => 'Current folder',
            'permissions' => $this->folderPermissionLabel($folder),
            'created'     => $folder->created_at?->format('Y/m/d H:i'),
            'updated'     => $folder->updated_at?->format('Y/m/d H:i'),
            'extra'       => $items.' '.($items === 1 ? 'item' : 'items'),
            'delete_note' => $this->folderDeleteNote($folder),
        ];
        $this->showInfoModal = true;
    }

    public function closeInfo(): void
    {
        $this->showInfoModal = false;
        $this->infoItem = null;
    }

    public function mediaOpenUrl(int $mediaId): string
    {
        return route('filament-file-explorer.media.show', ['scopeKey' => $this->scopeKey, 'media' => $mediaId]);
    }

    public function mediaDownloadUrl(int $mediaId): string
    {
        return route('filament-file-explorer.media.show', ['scopeKey' => $this->scopeKey, 'media' => $mediaId, 'download' => 1]);
    }

    public function mediaZipUrl(int $mediaId): string
    {
        return route('filament-file-explorer.media.zip-media', ['scopeKey' => $this->scopeKey, 'media' => $mediaId]);
    }

    public function folderZipUrl(int $folderId): string
    {
        return route('filament-file-explorer.media.zip-folder', ['scopeKey' => $this->scopeKey, 'folder' => $folderId, 'root' => $this->rootFolderId]);
    }

    protected function folderPathString(Folder $folder): string
    {
        $parts = [];
        $node = $folder;
        $depth = 0;
        while ($node && $depth < 50) {
            array_unshift($parts, $node->name);
            if ((int) $node->id === $this->rootFolderId) {
                break;
            }
            $node = $node->parent;
            $depth++;
        }

        return implode(' / ', $parts);
    }

    protected function folderSizeBytes(Folder $folder): int
    {
        $total = 0;
        foreach ($folder->getMedia(UploadRules::collection()) as $media) {
            $total += (int) $media->size;
        }
        foreach ($folder->children as $child) {
            $total += $this->folderSizeBytes($child);
        }

        return $total;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1073741824, 2).' GB';
    }

    protected function mediaPermissionLabel(Media $media): string
    {
        $state = app(FileExplorerAuthorizer::class)->mediaDeleteState($this->scopeKey, $media);

        if ($state['allowed']) {
            if ($state['remaining_seconds'] !== null) {
                return __('filament-file-explorer::file-explorer.permissions.deletable_with_reason', ['reason' => $state['reason']]);
            }

            return __('filament-file-explorer::file-explorer.permissions.read_write_delete');
        }

        return __('filament-file-explorer::file-explorer.permissions.read_write').' · '.($state['reason'] ?? __('filament-file-explorer::file-explorer.delete_not_allowed'));
    }

    protected function folderPermissionLabel(Folder $folder): string
    {
        if ((int) $folder->id === $this->rootFolderId) {
            return __('filament-file-explorer::file-explorer.permissions.read_write_root_locked');
        }

        $state = app(FileExplorerAuthorizer::class)->folderDeleteState($this->scopeKey, $folder);

        if ($state['allowed']) {
            return __('filament-file-explorer::file-explorer.permissions.read_write_delete');
        }

        return __('filament-file-explorer::file-explorer.permissions.read_write').' · '.($state['reason'] ?? __('filament-file-explorer::file-explorer.permissions.folder_delete_denied'));
    }

    protected function folderDeleteNote(Folder $folder): ?string
    {
        $state = app(FileExplorerAuthorizer::class)->folderDeleteState($this->scopeKey, $folder);

        return $state['reason'];
    }

    /**
     * @return array{
     *     allowed: bool,
     *     reason_code: string|null,
     *     reason: string|null,
     *     remaining_seconds: int|null,
     *     window_seconds: int,
     *     hint: string
     * }
     */
    public function getDeleteState(string $type, ?int $id = null): array
    {
        $docs = app(FileExplorerAuthorizer::class);

        abort_unless($docs->canAccess($this->scopeKey, $this->rootFolderId), 403);

        if ($type === 'file' && $id) {
            $media = Media::query()->findOrFail($id);
            $folder = Folder::query()->find($media->model_id);
            if ($folder) {
                $this->assertUnderRoot($folder);
            }

            $state = $docs->mediaDeleteState($this->scopeKey, $media);
            $state['hint'] = $state['allowed']
                ? (string) ($state['reason'] ?? __('filament-file-explorer::file-explorer.permissions.deletable'))
                : (string) ($state['reason'] ?? __('filament-file-explorer::file-explorer.delete_not_allowed'));

            return $state;
        }

        if ($type === 'folder' && $id) {
            $folder = Folder::query()->findOrFail($id);
            $this->assertUnderRoot($folder);
            $state = $docs->folderDeleteState($this->scopeKey, $folder);
            $state['hint'] = $state['allowed']
                ? __('filament-file-explorer::file-explorer.permissions.deletable')
                : (string) ($state['reason'] ?? __('filament-file-explorer::file-explorer.permissions.folder_delete_denied'));

            return $state;
        }

        return [
            'allowed'           => false,
            'reason_code'       => 'unknown',
            'reason'            => __('filament-file-explorer::file-explorer.nothing_selected'),
            'remaining_seconds' => null,
            'window_seconds'    => 0,
            'hint'              => __('filament-file-explorer::file-explorer.nothing_selected'),
        ];
    }

    public function loadFolders(): void
    {
        $withCounts = [
            'children',
            'media as file_explorer_count' => fn ($q) => $q->where('collection_name', UploadRules::collection()),
        ];

        if ($this->search != '') {
            $rootIds = $this->descendantFolderIdsIncludingRoot();

            $this->folders = Folder::query()
                ->whereIn('id', $rootIds)
                ->where('id', '!=', $this->rootFolderId)
                ->where('name', 'like', '%'.$this->search.'%')
                ->withCount($withCounts)
                ->get();

            $this->searchedFiles = Media::query()
                ->where('collection_name', UploadRules::collection())
                ->where('model_type', Folder::class)
                ->whereIn('model_id', $rootIds)
                ->where('name', 'like', '%'.$this->search.'%')
                ->get();

            return;
        }

        $this->folders = Folder::query()
            ->where('parent_id', $this->currentFolder->id)
            ->withCount($withCounts)
            ->get();
        $this->searchedFiles = null;
    }

    /**
     * Nested folder tree for the explorer sidebar — root is the primary folder.
     *
     * @return list<array{id:int,name:string,primary?:bool,children:list<array>}>
     */
    public function sidebarTree(): array
    {
        $ids = $this->descendantFolderIdsIncludingRoot();
        $folders = Folder::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        $byParent = $folders->groupBy(fn (Folder $f) => (int) ($f->parent_id ?? 0));

        $build = function (int $parentId) use (&$build, $byParent): array {
            return ($byParent->get($parentId) ?? collect())
                ->map(fn (Folder $folder) => [
                    'id'       => (int) $folder->id,
                    'name'     => (string) $folder->name,
                    'children' => $build((int) $folder->id),
                ])
                ->values()
                ->all();
        };

        $root = $folders->firstWhere('id', $this->rootFolderId);

        if (! $root) {
            return [];
        }

        return [[
            'id'       => (int) $root->id,
            'name'     => (string) $root->name,
            'primary'  => true,
            'children' => $build($this->rootFolderId),
        ]];
    }

    protected function descendantFolderIdsIncludingRoot(): array
    {
        return app(FolderTree::class)
            ->descendantFolderIdsIncludingRoot($this->rootFolderId);
    }

    public function createNewFolder(): void
    {
        abort_unless($this->ability('mkdir'), 403);
        $this->assertUnderRoot($this->currentFolder);

        $this->isCreatingNewFolder = true;
        $this->newFolderName = __('filament-file-explorer::file-explorer.folder_without_title');
        $this->dispatch('new-folder-created');
    }

    public function cancelNewFolder(): void
    {
        $this->isCreatingNewFolder = false;
        $this->newFolderName = '';
        $this->resetErrorBag('newFolderName');
    }

    public function saveNewFolder(): void
    {
        if (! $this->isCreatingNewFolder) {
            return;
        }

        abort_unless($this->ability('mkdir'), 403);
        $this->assertUnderRoot($this->currentFolder);

        $name = trim((string) $this->newFolderName);

        if ($name === '') {
            $this->cancelNewFolder();

            return;
        }

        $this->validate([
            'newFolderName' => [
                'required',
                'max:255',
                function ($attribute, $value, $fail) {
                    $slug = Str::slug(trim($value));
                    $existingFolder = Folder::query()
                        ->where('slug', $slug)
                        ->where('parent_id', $this->currentFolder?->id)
                        ->first();

                    if ($existingFolder) {
                        $fail(__('filament-file-explorer::file-explorer.folder_already_exists'));
                    }

                    $maxDepth = config('filament-file-explorer.folders.max_depth');

                    if ($maxDepth !== null && $this->currentFolder) {
                        if ($this->currentFolder->getDepth() >= $maxDepth - 1) {
                            $fail(__('filament-file-explorer::file-explorer.validation.max_folder_depth_exceeded', ['max' => $maxDepth]));
                        }
                    }
                },
            ],
        ]);

        $parent = $this->currentFolder;
        $newFolder = new Folder;
        $newFolder->name = $name;
        $slug = Str::slug($name);
        $newFolder->slug = $slug !== '' ? $slug : ('folder-'.Str::lower(Str::random(8)));
        $newFolder->parent_id = $parent->id;
        $newFolder->save();

        $this->newFolderName = '';
        $this->isCreatingNewFolder = false;
        $this->selectedFolders = [(int) $newFolder->id];
        $this->selectedFiles = [];
        $this->currentFolder = $parent->fresh(['children', 'parent']);
        $this->breadcrumb = $this->generateBreadcrumb($this->currentFolder);
        session([$this->sessionKey() => $parent->id]);
        $this->loadFolders();
        $this->dispatch('fe-folder-created', folderId: (int) $newFolder->id);
    }

    public function navigateToParent(): void
    {
        if ((int) $this->currentFolder->id === $this->rootFolderId) {
            return;
        }

        $this->search = '';
        $this->selectedFolders = [];
        $this->selectedFiles = [];
        $this->dispatch('fe-sel-cleared');

        if ($this->currentFolder->parent_id === null) {
            return;
        }

        $parentFolder = Folder::query()->find($this->currentFolder->parent_id);
        $this->assertUnderRoot($parentFolder);

        $this->currentFolder = $parentFolder;
        session([$this->sessionKey() => $parentFolder->id]);
        $this->breadcrumb = $this->generateBreadcrumb($this->currentFolder);
        $this->pushNavHistory((int) $parentFolder->id);
        $this->loadFolders();
    }

    public function navigateToFolder($folderId): void
    {
        $folder = Folder::query()->findOrFail($folderId);
        $this->assertUnderRoot($folder);

        $this->search = '';
        $this->selectedFolders = [];
        $this->selectedFiles = [];
        $this->dispatch('reset-folder');
        $this->dispatch('fe-sel-cleared');

        $this->currentFolder = $folder;
        $this->breadcrumb = $this->generateBreadcrumb($this->currentFolder);
        $this->loadFolders();
        $this->pushNavHistory((int) $folder->id);

        session([$this->sessionKey() => $folder->id]);
    }

    public function navigateToBreadcrumb($breadcrumbIndex): void
    {
        $this->search = '';
        $this->selectedFolders = [];
        $this->selectedFiles = [];
        $this->dispatch('fe-sel-cleared');

        $this->breadcrumb = array_slice($this->breadcrumb, 0, $breadcrumbIndex + 1);
        $this->currentFolder = end($this->breadcrumb);
        $this->assertUnderRoot($this->currentFolder);

        session([$this->sessionKey() => $this->currentFolder->id]);
        $this->pushNavHistory((int) $this->currentFolder->id);
        $this->loadFolders();
    }

    protected function generateBreadcrumb($folder)
    {
        $breadcrumb = [];

        while ($folder) {
            array_unshift($breadcrumb, $folder);

            if ((int) $folder->id === $this->rootFolderId) {
                break;
            }

            $folder = $folder->parent;
        }

        return $breadcrumb;
    }

    public function updatedFiles(): void
    {
        $docs = app(FileExplorerAuthorizer::class);

        abort_unless($this->ability('upload'), 403);

        $targetId = $this->uploadTargetFolderId ?? (int) $this->currentFolder->id;
        $this->uploadTargetFolderId = null;

        $folder = Folder::query()->findOrFail($targetId);
        $this->assertUnderRoot($folder);

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->reset('files');
            $this->fileInputKey++;
            $this->dispatch('fe-upload-settled');

            Notification::make()
                ->danger()
                ->title(__('filament-file-explorer::file-explorer.upload_invalid'))
                ->body(collect($e->validator->errors()->all())->first() ?: __('filament-file-explorer::file-explorer.validation.file_not_allowed'))
                ->send();

            throw $e;
        }

        $actor = auth()->user();
        $uploaded = 0;

        foreach ($this->files as $file) {
            if (! $file) {
                continue;
            }

            $original = method_exists($file, 'getClientOriginalName')
                ? $file->getClientOriginalName()
                : (string) $file;

            $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION) ?: 'bin');
            $name = pathinfo($original, PATHINFO_FILENAME);
            $safe = strtolower((Str::slug($name) ?: 'file').'.'.$extension);

            $folder
                ->addMedia($file)
                ->usingName($original)
                ->usingFileName($safe)
                ->withCustomProperties([
                    'user_id'          => $actor?->getAuthIdentifier(),
                    'uploaded_by_type' => $actor ? $actor::class : null,
                    'uploaded_by_id'   => $actor?->getAuthIdentifier(),
                ])
                ->toMediaCollection(UploadRules::collection());

            $uploaded++;
        }

        $this->reset('files');
        $this->fileInputKey++;

        if ((int) $folder->id === (int) $this->currentFolder->id) {
            $this->currentFolder = $folder->fresh(['children', 'parent']);
        } else {
            $this->currentFolder = $this->currentFolder->fresh(['children', 'parent']);
        }

        $this->loadFolders();

        $this->dispatch('fe-upload-settled');

        if ($uploaded > 0) {
            Notification::make()
                ->success()
                ->title(__('filament-file-explorer::file-explorer.uploaded'))
                ->body($uploaded.' فایل در «'.$folder->name.'»')
                ->send();
        }
    }

    public function prepareUploadToFolder(int $folderId): void
    {
        $folder = Folder::query()->findOrFail($folderId);
        $this->assertUnderRoot($folder);
        abort_unless($this->ability('upload'), 403);
        $this->uploadTargetFolderId = $folderId;
    }

    public function deleteSelected(array $folders = [], array $files = []): void
    {
        if ($folders !== [] || $files !== []) {
            $this->selectedFolders = array_values(array_map('intval', $folders));
            $this->selectedFiles = array_values(array_map('intval', $files));
        }

        $this->deleteItems();
    }

    public function deleteItems(): void
    {
        $docs = app(FileExplorerAuthorizer::class);

        abort_unless($docs->canAccess($this->scopeKey, $this->rootFolderId), 403);

        $fileIds = array_values(array_unique(array_map('intval', (array) $this->selectedFiles)));
        $folderIds = array_values(array_unique(array_map('intval', (array) $this->selectedFolders)));

        $deleted = 0;

        foreach ($fileIds as $fileId) {
            $media = Media::query()->find($fileId);

            if (! $media) {
                continue;
            }

            $state = $docs->mediaDeleteState($this->scopeKey, $media);

            if (! $state['allowed']) {
                Notification::make()
                    ->warning()
                    ->title(MediaLabel::display($media))
                    ->body($state['reason'] ?? __('filament-file-explorer::file-explorer.file_delete_denied'))
                    ->send();

                continue;
            }

            $media->delete();
            $deleted++;
        }

        foreach ($folderIds as $folderId) {
            if ((int) $folderId === $this->rootFolderId) {
                Notification::make()
                    ->warning()
                    ->title(__('filament-file-explorer::file-explorer.root_not_deletable'))
                    ->send();

                continue;
            }

            $folder = Folder::query()->find($folderId);

            if (! $folder) {
                continue;
            }

            $this->assertUnderRoot($folder);
            $folderState = $docs->folderDeleteState($this->scopeKey, $folder);

            if (! $folderState['allowed']) {
                Notification::make()
                    ->warning()
                    ->title($folder->name)
                    ->body($folderState['reason'] ?? __('filament-file-explorer::file-explorer.folder_delete_denied'))
                    ->send();

                continue;
            }

            $this->deleteFolderRecursive($folder);
            $deleted++;
        }

        if ($deleted > 0) {
            Notification::make()
                ->success()
                ->title($deleted === 1
                    ? __('filament-file-explorer::file-explorer.item_deleted')
                    : __('filament-file-explorer::file-explorer.items_deleted', ['count' => $deleted]))
                ->send();
        }

        $this->selectedFiles = [];
        $this->selectedFolders = [];
        $this->dispatch('reset-media');
        $this->dispatch('reset-folder');
        $this->dispatch('clear-all-selections');
        $this->dispatch('fe-sel-cleared');

        if (! Folder::query()->find($this->currentFolder->id)) {
            $this->currentFolder = Folder::with(['children', 'parent'])->findOrFail($this->rootFolderId);
            session([$this->sessionKey() => $this->rootFolderId]);
            $this->breadcrumb = $this->generateBreadcrumb($this->currentFolder);
        } else {
            $this->currentFolder = $this->currentFolder->fresh(['children', 'parent']);
        }

        $this->loadFolders();
    }

    protected function deleteFolderRecursive(Folder $folder): void
    {
        foreach ($folder->children as $child) {
            $this->deleteFolderRecursive($child);
        }

        foreach ($folder->getMedia(UploadRules::collection()) as $media) {
            $media->delete();
        }

        $folder->delete();
    }

    public function moveItemsToFolder($targetFolderId, $folderIds = [], $fileIds = []): void
    {
        $docs = app(FileExplorerAuthorizer::class);

        abort_unless($this->ability('move'), 403);

        $targetFolder = Folder::query()->find($targetFolderId);

        if (! $targetFolder) {
            return;
        }

        $this->assertUnderRoot($targetFolder);

        $folderIds = array_values(array_unique(array_map('intval', (array) $folderIds)));
        $fileIds = array_values(array_unique(array_map('intval', (array) $fileIds)));

        foreach ($folderIds as $folderId) {
            if ((int) $folderId === $this->rootFolderId) {
                continue;
            }

            if ((int) $folderId === (int) $targetFolderId || $this->folderIsAncestorOf((int) $folderId, (int) $targetFolderId)) {
                continue;
            }

            $folder = Folder::query()->find($folderId);

            if (! $folder) {
                continue;
            }

            $this->assertUnderRoot($folder);
            $folder->parent_id = $targetFolderId;
            $folder->save();
        }

        foreach ($fileIds as $fileId) {
            $media = Media::query()->find($fileId);

            if (! $media) {
                continue;
            }

            $source = Folder::query()->find($media->model_id);

            if ($source) {
                $this->assertUnderRoot($source);
            }

            $media->model_id = $targetFolderId;
            $media->save();
        }

        $this->selectedFolders = [];
        $this->selectedFiles = [];
        $this->dispatch('reset-media');
        $this->dispatch('reset-folder');
        $this->dispatch('fe-sel-cleared');
        $this->currentFolder = $this->currentFolder->fresh(['children']);
        $this->loadFolders();
    }

    public function copyItemsToFolder($targetFolderId, $folderIds = [], $fileIds = []): void
    {
        $docs = app(FileExplorerAuthorizer::class);

        abort_unless($this->ability('copy'), 403);

        $targetFolder = Folder::query()->find($targetFolderId);

        if (! $targetFolder) {
            return;
        }

        $this->assertUnderRoot($targetFolder);

        $folderIds = array_values(array_unique(array_map('intval', (array) $folderIds)));
        $fileIds = array_values(array_unique(array_map('intval', (array) $fileIds)));

        foreach ($folderIds as $folderId) {
            if ((int) $folderId === $this->rootFolderId) {
                continue;
            }

            if ((int) $folderId === (int) $targetFolderId || $this->folderIsAncestorOf((int) $folderId, (int) $targetFolderId)) {
                continue;
            }

            $source = Folder::query()->find($folderId);

            if (! $source) {
                continue;
            }

            $this->assertUnderRoot($source);
            $this->copyFolderRecursive($source, $targetFolder);
        }

        foreach ($fileIds as $fileId) {
            $media = Media::query()->find($fileId);

            if (! $media) {
                continue;
            }

            $source = Folder::query()->find($media->model_id);

            if ($source) {
                $this->assertUnderRoot($source);
            }

            $this->duplicateMedia($media, $targetFolder);
        }

        $this->dispatch('fe-sel-cleared');
        $this->currentFolder = $this->currentFolder->fresh(['children']);
        $this->loadFolders();
        Notification::make()->success()->title(__('filament-file-explorer::file-explorer.copied'))->send();
    }

    /**
     * True when $ancestorId is the same as or an ancestor of $descendantId.
     */
    protected function folderIsAncestorOf(int $ancestorId, int $descendantId): bool
    {
        if ($ancestorId === $descendantId) {
            return true;
        }

        $folder = Folder::query()->find($descendantId);
        $depth = 0;

        while ($folder && $folder->parent_id && $depth < 50) {
            if ((int) $folder->parent_id === $ancestorId) {
                return true;
            }

            $folder = Folder::query()->find($folder->parent_id);
            $depth++;
        }

        return false;
    }


    /**
     * @return array{scopeKey: string, rootFolderId: int, routePrefix: string}
     */
    public function urlConfig(): array
    {
        return [
            'scopeKey'     => $this->scopeKey,
            'rootFolderId' => $this->rootFolderId,
            'routePrefix'  => route('filament-file-explorer.media.show', ['scopeKey' => $this->scopeKey, 'media' => 0]),
        ];
    }

    public function render()
    {
        return view('filament-file-explorer::livewire.file-explorer');
    }
}
