# Configuration

Publish config:

```bash
php artisan vendor:publish --tag=filament-file-explorer-config
```

Key options in `config/filament-file-explorer.php`:

| Key | Description |
|-----|-------------|
| `authorizer` | FQCN implementing `FileExplorerAuthorizer` |
| `collection` | Spatie media collection name (default: `file-explorer`) |
| `upload.max_size_kb` | Max upload size |
| `upload.allowed_extensions` | Allowed file extensions |
| `folders.max_depth` | Max nested folder depth |
| `routes.prefix` | Media download route prefix |

Customize views:

```bash
php artisan vendor:publish --tag=filament-file-explorer-views
```
