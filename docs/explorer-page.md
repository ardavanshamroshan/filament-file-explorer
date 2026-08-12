# Explorer page

Use `InteractsWithFileExplorer` or extend `FileExplorerPage`.

![Explorer](../images/explorer-grid-light.svg)

```php
use Ardavan\FilamentFileExplorer\Pages\Concerns\InteractsWithFileExplorer;

protected string $view = 'filament-file-explorer::filament.pages.file-explorer';

protected function fileExplorerScopeKey(): string
{
    return 'vault.'.$this->record->id;
}

protected function resolveFileExplorerRootFolderId(): int
{
    return (int) $this->record->folder_id;
}
```

Deep-link to a folder: append `?folder={id}` to the page URL.
