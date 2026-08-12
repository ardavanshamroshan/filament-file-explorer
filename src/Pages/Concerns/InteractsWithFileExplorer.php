<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Pages\Concerns;

trait InteractsWithFileExplorer
{
    public int $rootFolderId = 0;

    abstract protected function fileExplorerScopeKey(): string;

    abstract protected function resolveFileExplorerRootFolderId(): int;

    public function mountFileExplorer(): void
    {
        $this->rootFolderId = $this->resolveFileExplorerRootFolderId();

        abort_unless(
            app(\Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer::class)
                ->canAccess($this->fileExplorerScopeKey(), $this->rootFolderId),
            403
        );
    }
}
