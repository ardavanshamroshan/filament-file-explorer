<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Support;

use Ardavan\FilamentFileExplorer\Models\Folder;

final class FolderTree
{
    public function isUnderRoot(Folder $folder, int $rootFolderId): bool
    {
        if ((int) $folder->id === $rootFolderId) {
            return true;
        }

        $current = $folder;

        while ($current->parent_id !== null) {
            if ((int) $current->parent_id === $rootFolderId) {
                return true;
            }

            $current = Folder::query()->find($current->parent_id);

            if (! $current) {
                return false;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public function descendantFolderIdsIncludingRoot(int $rootFolderId): array
    {
        $ids = [$rootFolderId];
        $frontier = [$rootFolderId];

        while ($frontier !== []) {
            $children = Folder::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $frontier = array_values(array_diff($children, $ids));
            $ids = array_values(array_unique([...$ids, ...$children]));
        }

        return $ids;
    }

    public function getDepth(Folder $folder): int
    {
        $depth = 0;
        $current = $folder;

        while ($current->parent_id !== null && $depth < 100) {
            $depth++;
            $current = Folder::query()->find($current->parent_id);

            if (! $current) {
                break;
            }
        }

        return $depth;
    }
}
