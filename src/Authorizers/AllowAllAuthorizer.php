<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Authorizers;

use Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer;
use Ardavan\FilamentFileExplorer\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AllowAllAuthorizer implements FileExplorerAuthorizer
{
    public function canAccess(string $scopeKey, int $rootFolderId): bool
    {
        return true;
    }

    public function abilities(string $scopeKey, int $rootFolderId): array
    {
        return [
            'browse'       => true,
            'search'       => true,
            'getInfo'      => true,
            'download'     => true,
            'upload'       => true,
            'mkdir'        => true,
            'rename'       => true,
            'move'         => true,
            'copy'         => true,
            'delete'       => true,
            'deleteFolder' => true,
        ];
    }

    public function mediaDeleteState(string $scopeKey, Media $media): array
    {
        return [
            'allowed'           => true,
            'reason_code'       => null,
            'reason'            => null,
            'remaining_seconds' => null,
            'window_seconds'    => 0,
        ];
    }

    public function folderDeleteState(string $scopeKey, Folder $folder): array
    {
        return [
            'allowed'           => true,
            'reason_code'       => null,
            'reason'            => null,
            'remaining_seconds' => null,
            'window_seconds'    => 0,
        ];
    }
}
