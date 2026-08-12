<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Support;

use Ardavan\FilamentFileExplorer\Models\Folder;
use Illuminate\Support\Str;

class FileExplorerManager
{
    public function collection(): string
    {
        return UploadRules::collection();
    }

    public function createRoot(string $name, ?string $slug = null): Folder
    {
        $slug ??= Str::slug($name) ?: ('folder-'.Str::lower(Str::random(8)));

        return Folder::query()->create([
            'name'      => $name,
            'slug'      => $slug,
            'parent_id' => null,
        ]);
    }

    public function createChild(Folder $parent, string $name, ?string $slug = null): Folder
    {
        $slug ??= Str::slug($name) ?: ('folder-'.Str::lower(Str::random(8)));

        return Folder::query()->create([
            'name'      => $name,
            'slug'      => $slug,
            'parent_id' => $parent->id,
        ]);
    }
}
