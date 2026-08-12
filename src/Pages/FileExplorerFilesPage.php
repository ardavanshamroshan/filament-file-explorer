<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Pages;

use Ardavan\FilamentFileExplorer\Models\Concerns\HasFileExplorer;
use Ardavan\FilamentFileExplorer\Pages\Concerns\InteractsWithFileExplorer;
use Ardavan\FilamentFileExplorer\Support\HasFileExplorerModel;
use Ardavan\FilamentFileExplorer\Tables\Concerns\InteractsWithFileExplorerTable;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

abstract class FileExplorerFilesPage extends Page implements HasTable
{
    use InteractsWithFileExplorer;
    use InteractsWithFileExplorerTable;
    use InteractsWithRecord;
    use InteractsWithTable;

    protected string $view = 'filament-file-explorer::filament.pages.file-explorer-files';

    public static function getNavigationLabel(): string
    {
        return __('filament-file-explorer.file-explorer.files');
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->mountFileExplorer();
    }

    public function table(Table $table): Table
    {
        return $this->configureFileExplorerTable($table);
    }

    protected function fileExplorerScopeKey(): string
    {
        $record = HasFileExplorerModel::assert($this->getRecord());

        return $record->fileExplorerScopeKey();
    }

    protected function resolveFileExplorerRootFolderId(): int
    {
        return $this->resolveFileExplorerRootFolderIdFromRecord($this->getRecord());
    }

    protected function resolveFileExplorerRootFolderIdFromRecord(mixed $record): int
    {
        /** @var HasFileExplorer $record */
        $record = HasFileExplorerModel::assert($record);
        $id = $record->fileExplorerRootFolderId();

        abort_unless($id, 404);

        return $id;
    }

    protected function fileExplorerRootFolderId(): int
    {
        return $this->rootFolderId;
    }

    protected function fileExplorerExplorerPageName(): string
    {
        return 'files';
    }

    protected function fileExplorerExplorerUrl(?int $folderId = null): string
    {
        $url = static::getResource()::getUrl(
            $this->fileExplorerExplorerPageName(),
            ['record' => $this->getRecord()],
        );

        return $folderId ? $url.'?folder='.$folderId : $url;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('explorer')
                ->label(__('filament-file-explorer.file-explorer.explorer'))
                ->icon('heroicon-o-folder-open')
                ->url(fn (): string => $this->fileExplorerExplorerUrl()),
        ];
    }
}
