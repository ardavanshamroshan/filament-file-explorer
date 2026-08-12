# Files table

Use `InteractsWithFileExplorerTable` on any Filament page implementing `HasTable`.

![Files table](../images/files-table.svg)

```php
public function table(Table $table): Table
{
    return $this->configureFileExplorerTable($table);
}
```

Implement:

- `fileExplorerScopeKey()`
- `fileExplorerRootFolderId()`
- `fileExplorerExplorerUrl(?int $folderId = null)`
