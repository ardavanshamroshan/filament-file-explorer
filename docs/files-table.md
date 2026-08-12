# Files table

Generate:

```bash
php artisan filament-file-explorer:make-page ProjectResource --list
```

```php
use Ardavan\FilamentFileExplorer\Pages\FileExplorerFilesPage;

class ListProjectFiles extends FileExplorerFilesPage
{
    protected static string $resource = ProjectResource::class;
}
```

Or use `InteractsWithFileExplorerTable` on a custom page.

![Files table](../images/files-table.svg)

Required hooks (already implemented on `FileExplorerFilesPage` when model uses `HasFileExplorer`):

- `fileExplorerScopeKey()`
- `fileExplorerRootFolderId()`
- `fileExplorerExplorerUrl(?int $folderId = null)`
