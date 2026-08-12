<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Contracts;

use Ardavan\FilamentFileExplorer\Models\Folder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

interface FileExplorerAuthorizer
{
    public function canAccess(string $scopeKey, int $rootFolderId): bool;

    /**
     * @return array{
     *     browse: bool,
     *     search: bool,
     *     getInfo: bool,
     *     download: bool,
     *     upload: bool,
     *     mkdir: bool,
     *     rename: bool,
     *     move: bool,
     *     copy: bool,
     *     delete: bool,
     *     deleteFolder: bool
     * }
     */
    public function abilities(string $scopeKey, int $rootFolderId): array;

    /**
     * @return array{
     *     allowed: bool,
     *     reason_code: string|null,
     *     reason: string|null,
     *     remaining_seconds: int|null,
     *     window_seconds: int
     * }
     */
    public function mediaDeleteState(string $scopeKey, Media $media): array;

    /**
     * @return array{
     *     allowed: bool,
     *     reason_code: string|null,
     *     reason: string|null,
     *     remaining_seconds: int|null,
     *     window_seconds: int
     * }
     */
    public function folderDeleteState(string $scopeKey, Folder $folder): array;
}
