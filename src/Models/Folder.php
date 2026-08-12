<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Models;

use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Ardavan\FilamentFileExplorer\Support\UploadRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Folder extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function getTable(): string
    {
        return (string) config('filament-file-explorer.folders.table', 'file_explorer_folders');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getMorphClass(): string
    {
        $legacy = config('filament-file-explorer.morph_class');

        return is_string($legacy) && $legacy !== ''
            ? $legacy
            : parent::getMorphClass();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(UploadRules::collection());
    }

    public function getDepth(): int
    {
        return app(FolderTree::class)->getDepth($this);
    }
}
