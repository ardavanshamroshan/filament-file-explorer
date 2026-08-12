<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Tables\Concerns;

use Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer;
use Ardavan\FilamentFileExplorer\Models\Folder;
use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Ardavan\FilamentFileExplorer\Support\UploadRules;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait InteractsWithFileExplorerTable
{
    abstract protected function fileExplorerScopeKey(): string;

    abstract protected function fileExplorerRootFolderId(): int;

    abstract protected function fileExplorerExplorerUrl(?int $folderId = null): string;

    protected function fileExplorerMediaQuery(): Builder
    {
        $rootId = $this->fileExplorerRootFolderId();
        $folderIds = app(FolderTree::class)->descendantFolderIdsIncludingRoot($rootId);

        return Media::query()
            ->where('model_type', (new Folder)->getMorphClass())
            ->whereIn('model_id', $folderIds)
            ->where('collection_name', UploadRules::collection())
            ->latest('id');
    }

    public function configureFileExplorerTable(Table $table): Table
    {
        $scopeKey = $this->fileExplorerScopeKey();
        $rootId = $this->fileExplorerRootFolderId();
        $authorizer = app(FileExplorerAuthorizer::class);

        return $table
            ->query($this->fileExplorerMediaQuery())
            ->emptyStateHeading(__('filament-file-explorer::file-explorer.no_files'))
            ->emptyStateDescription(__('filament-file-explorer::file-explorer.open_explorer_hint'))
            ->emptyStateActions([
                Action::make('openExplorer')
                    ->label(__('filament-file-explorer::file-explorer.explorer'))
                    ->icon('heroicon-o-folder-open')
                    ->url(fn (): string => $this->fileExplorerExplorerUrl()),
            ])
            ->columns([
                ImageColumn::make('preview')
                    ->label(__('filament-file-explorer::file-explorer.preview'))
                    ->getStateUsing(function (Media $record): ?string {
                        if (! str_starts_with((string) $record->mime_type, 'image/')) {
                            return null;
                        }

                        try {
                            return $record->getUrl();
                        } catch (\Throwable) {
                            return null;
                        }
                    })
                    ->circular()
                    ->imageSize(36)
                    ->toggleable(),

                TextColumn::make('file_name')
                    ->label(__('filament-file-explorer::file-explorer.file_name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(60),

                TextColumn::make('size')
                    ->label(__('filament-file-explorer::file-explorer.size'))
                    ->sortable()
                    ->formatStateUsing(fn ($state): string => Number::fileSize((int) $state, precision: 1))
                    ->alignEnd(),

                TextColumn::make('mime_type')
                    ->label(__('filament-file-explorer::file-explorer.type'))
                    ->badge()
                    ->formatStateUsing(function (Media $record): string {
                        $ext = strtoupper((string) pathinfo((string) $record->file_name, PATHINFO_EXTENSION));

                        return $ext !== '' ? $ext : ((string) $record->mime_type ?: '—');
                    })
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('filament-file-explorer::file-explorer.created_at'))
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('download')
                        ->label(__('filament-file-explorer::file-explorer.download'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn (Media $record): string => route('filament-file-explorer.media.show', [
                            'scopeKey' => $scopeKey,
                            'media'    => $record->id,
                            'download' => 1,
                        ]))
                        ->openUrlInNewTab()
                        ->visible(fn (): bool => ($authorizer->abilities($scopeKey, $rootId)['download'] ?? false)),

                    Action::make('openInExplorer')
                        ->label(__('filament-file-explorer::file-explorer.open_in_explorer'))
                        ->icon('heroicon-o-folder-open')
                        ->url(fn (Media $record): string => $this->fileExplorerExplorerUrl((int) $record->model_id)),

                    Action::make('delete')
                        ->label(__('filament-file-explorer::file-explorer.delete'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Media $record): bool => $authorizer->mediaDeleteState($scopeKey, $record)['allowed'])
                        ->action(function (Media $record): void {
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title(__('filament-file-explorer::file-explorer.deleted'))
                                ->send();
                        }),
                ])
                    ->label(__('filament-file-explorer::file-explorer.actions'))
                    ->button()
                    ->color('gray'),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50]);
    }
}
