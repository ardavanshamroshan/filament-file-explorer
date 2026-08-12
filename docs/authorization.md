# Authorization

Implement `Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer`:

```php
final class ProjectFileExplorerAuthorizer implements FileExplorerAuthorizer
{
    public function canAccess(string $scopeKey, int $rootFolderId): bool
    {
        return auth()->check();
    }

    public function abilities(string $scopeKey, int $rootFolderId): array
    {
        return [
            'browse' => true,
            'search' => true,
            'getInfo' => true,
            'download' => true,
            'upload' => auth()->user()?->can('upload-files') ?? false,
            'mkdir' => true,
            'rename' => true,
            'move' => true,
            'copy' => true,
            'delete' => true,
            'deleteFolder' => true,
        ];
    }

    public function mediaDeleteState(string $scopeKey, Media $media): array { /* ... */ }
    public function folderDeleteState(string $scopeKey, Folder $folder): array { /* ... */ }
}
```

Register in `AppServiceProvider`:

```php
$this->app->singleton(FileExplorerAuthorizer::class, ProjectFileExplorerAuthorizer::class);
```

Use `scopeKey` to isolate sessions and clipboard per host record (e.g. `project.42`).
