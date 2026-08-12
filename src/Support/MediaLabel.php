<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MediaLabel
{
    public static function display(Media $media): string
    {
        $ext = strtolower((string) pathinfo((string) $media->file_name, PATHINFO_EXTENSION));
        $base = trim((string) ($media->name ?: pathinfo((string) $media->file_name, PATHINFO_FILENAME)));

        if ($base === '') {
            return (string) $media->file_name;
        }

        if ($ext === '') {
            return $base;
        }

        if (str_ends_with(strtolower($base), '.'.$ext)) {
            return $base;
        }

        return $base.'.'.$ext;
    }
}
