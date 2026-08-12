<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Support;

use Illuminate\Http\UploadedFile;

final class UploadRules
{
    public static function collection(): string
    {
        return (string) config('filament-file-explorer.collection', 'file-explorer');
    }

    public static function maxSizeKb(): int
    {
        return (int) config('filament-file-explorer.upload.max_size_kb', 51200);
    }

    /**
     * @return list<string>
     */
    public static function acceptedExtensions(): array
    {
        return config('filament-file-explorer.upload.allowed_extensions', []);
    }

    /**
     * @return list<string>
     */
    public static function acceptedMimeTypes(): array
    {
        return config('filament-file-explorer.upload.allowed_mime_types', []);
    }

    /**
     * @return list<string>
     */
    public static function acceptedFileTypes(): array
    {
        return array_values(array_unique([
            ...self::acceptedMimeTypes(),
            ...array_map(fn (string $ext): string => '.'.$ext, self::acceptedExtensions()),
        ]));
    }

    public static function isAllowedUpload(UploadedFile $file): bool
    {
        $mime = (string) $file->getMimeType();
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (in_array($mime, self::acceptedMimeTypes(), true)) {
            return true;
        }

        if (! in_array($ext, self::acceptedExtensions(), true)) {
            return false;
        }

        return $mime === ''
            || $mime === 'application/octet-stream'
            || str_starts_with($mime, 'application/x-');
    }
}
