<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Pages;

use Ardavan\FilamentFileExplorer\Models\Concerns\HasFileExplorer;
use Ardavan\FilamentFileExplorer\Pages\Concerns\InteractsWithFileExplorer;
use Ardavan\FilamentFileExplorer\Support\HasFileExplorerModel;
use Filament\Actions\Action;
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
        $record = $this->getRecord();

        if (HasFileExplorerModel::uses($record)) {
            /** @var HasFileExplorer $record */
            return $record->fileExplorerScopeKey();
        }

        return static::class.'.'.$record->getKey();
    }

    protected function resolveFileExplorerRootFolderId(): int
    {
        return $this->resolveFileExplorerRootFolderIdFromRecord($this->getRecord());
    }

    /**
     * Override when the model does not use HasFileExplorer.
     */
    protected function resolveFileExplorerRootFolderIdFromRecord(mixed $record): int
    {
        /** @var HasFileExplorer $record */
        $record = HasFileExplorerModel::assert($record);
        $id = $record->fileExplorerRootFolderId();

        abort_unless($id, 404);

        return $id;
    }

    protected function fileExplorerFilesPageName(): string
    {
        return 'files-list';
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        try {
            $actions[] = Action::make('filesList')
                ->label(__('filament-file-explorer.file-explorer.files'))
                ->icon('heroicon-o-table-cells')
                ->url(fn (): string => static::getResource()::getUrl(
                    $this->fileExplorerFilesPageName(),
                    ['record' => $this->getRecord()],
                ));
        } catch (\Throwable) {
            // Files list page may not be registered.
        }

        return $actions;
    }
}
