<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Pages;

use Ardavan\FilamentFileExplorer\Pages\Concerns\InteractsWithFileExplorer;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

abstract class FileExplorerPage extends Page
{
    use InteractsWithFileExplorer;
    use InteractsWithRecord;

    protected string $view = 'filament-file-explorer::filament.pages.file-explorer';

    public static function getNavigationLabel(): string
    {
        return __('filament-file-explorer.file-explorer.explorer');
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountFileExplorer();
    }

    protected function fileExplorerScopeKey(): string
    {
        return static::class.'.'.$this->getRecord()->getKey();
    }

    protected function resolveFileExplorerRootFolderId(): int
    {
        return $this->resolveFileExplorerRootFolderIdFromRecord($this->getRecord());
    }

    abstract protected function resolveFileExplorerRootFolderIdFromRecord(mixed $record): int;
}
