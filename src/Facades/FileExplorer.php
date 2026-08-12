<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string collection()
 * @method static \Ardavan\FilamentFileExplorer\Models\Folder createRoot(string $name, ?string $slug = null)
 *
 * @see \Ardavan\FilamentFileExplorer\Support\FileExplorerManager
 */
class FileExplorer extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'filament-file-explorer';
    }
}
