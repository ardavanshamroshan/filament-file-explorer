<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class MimeIcon
{
    /** @var list<string> */
    private const KNOWN = ['pdf', 'docx', 'xlsx', 'pptx', 'zip', 'audio', 'video', 'file'];

    public static function forMedia(Media $media): string
    {
        return self::resolve(
            (string) pathinfo((string) $media->file_name, PATHINFO_EXTENSION),
            (string) $media->mime_type,
        );
    }

    public static function resolve(?string $extension = null, ?string $mime = null): string
    {
        $ext = strtolower(ltrim((string) $extension, '.'));
        $mime = strtolower((string) $mime);

        $byExt = match ($ext) {
            'pdf' => 'pdf',
            'doc', 'docx', 'rtf', 'odt', 'txt' => 'docx',
            'xls', 'xlsx', 'csv', 'ods' => 'xlsx',
            'ppt', 'pptx', 'odp' => 'pptx',
            'zip', 'rar', '7z', 'gz', 'tar' => 'zip',
            'mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a', 'wma' => 'audio',
            'mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'm4v' => 'video',
            default => null,
        };

        if ($byExt !== null) {
            return $byExt;
        }

        if (str_starts_with($mime, 'audio/')) {
            return 'audio';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        return match ($mime) {
            'application/pdf' => 'pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/rtf',
            'text/plain' => 'docx',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv' => 'xlsx',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/zip',
            'application/x-zip-compressed',
            'application/x-rar-compressed',
            'application/vnd.rar',
            'application/x-7z-compressed',
            'application/gzip',
            'application/x-tar' => 'zip',
            default => 'file',
        };
    }

    public static function isKnown(string $icon): bool
    {
        return in_array($icon, self::KNOWN, true);
    }
}
