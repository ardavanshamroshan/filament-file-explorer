<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Resources\Concerns;

use Filament\Actions\Action;

/**
 * Optional helpers for Filament resources that register File Explorer pages.
 *
 * Page classes are generated into the app via:
 * `php artisan filament-file-explorer:make-page {Resource}`
 *
 * Do not publish stubs unless you want to customize generated output.
 */
trait HasFileExplorerResource
{
    /**
     * @return array<string, mixed>
     */
    public static function getFileExplorerPages(
        string $explorerPageClass,
        ?string $filesPageClass = null,
    ): array {
        $pages = [
            'files' => $explorerPageClass::route('/{record}/files'),
        ];

        if ($filesPageClass !== null) {
            $pages['files-list'] = $filesPageClass::route('/{record}/files-list');
        }

        return $pages;
    }

    public static function openFileExplorerAction(string $name = 'files'): Action
    {
        return Action::make($name)
            ->label(__('filament-file-explorer.file-explorer.explorer'))
            ->icon('heroicon-o-folder-open')
            ->url(fn ($record): string => static::getUrl('files', ['record' => $record]));
    }
}
