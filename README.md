# Filament File Explorer

Finder-style file explorer for **Filament v4 and v5**, powered by **Spatie Media Library**.

![Explorer grid view](docs/images/explorer-grid.png)

## Screenshots

### Grid view

Icons with MIME badges, sidebar tree, and responsive toolbar.

![Explorer grid view](docs/images/explorer-grid.png)

### List / details view

Name, kind, size, and date — with the Get Info inspector open.

![Explorer list view](docs/images/explorer-list.png)

### Get Info

Folder and file metadata in a side inspector.

![Get Info panel](docs/images/explorer-get-info.png)

### Context menu

Right-click empty space or items for new folder, upload, paste, view, and sort.

![Context menu](docs/images/explorer-context-menu.png)

### Files table

Filament table page for searching and managing files across the scoped tree.

![Files table](docs/images/files-table.png)

## Features

- macOS Finder-style UI: sidebar tree, breadcrumbs, back/forward navigation
- View modes: icons (grid), list, columns (table), details
- Clipboard: cut, copy, paste folders and files
- Multi-select with marquee selection
- Context menu, drag-and-drop upload, folder zip download
- Responsive toolbar — overflow actions collapse into a **⋮** menu on narrow widths
- MIME icons for PDF, Office, zip, audio, video (+ generic file)
- File labels always show the extension (e.g. `readme.txt`)
- Dark-mode readable file names
- Uploads stored in the configured Spatie collection (`file-explorer` by default)
- **Filament plugin** with auto-loaded CSS/JS assets
- **`HasFileExplorer` model trait** — auto root folder + scope key
- Thin **resource page stubs** — generate only what you need; publish stubs only to customize
- Reusable **files table** page
- **Form picker** field to browse files in modals
- Generic **`scopeKey` + `rootFolderId`** API — no domain coupling
- Authorization via **`FileExplorerAuthorizer`** contract
- Translations: en, fa, ar, de, nl, fr, es, pt, tr, ru, zh_CN, ja, it, hi, id

## Requirements

| Package | Version |
|---------|---------|
| PHP | 8.2+ |
| Laravel | 11, 12, or 13 |
| Filament | 4.x or 5.x |
| Livewire | 3.x (Filament 4) or 4.x (Filament 5) |
| Spatie Media Library | 11.x |

## Installation

```bash
composer require ardavan/filament-file-explorer:"^0.5" -W

php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan filament-file-explorer:install --migrate
```

Register the plugin in your panel provider:

```php
use Ardavan\FilamentFileExplorer\FilamentFileExplorerPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->viteTheme('resources/css/filament/admin/theme.css')
        ->plugin(FilamentFileExplorerPlugin::make());
}
```

### Theme (required)

Add package views to your Filament theme so Tailwind utilities compile:

```css
/* resources/css/filament/admin/theme.css */
@import '../../../../vendor/filament/filament/resources/css/theme.css';

@source '../../../../app/Filament/**/*';
@source '../../../../resources/views/filament/**/*';
@source '../../../../vendor/ardavan/filament-file-explorer/resources/views/**/*.blade.php';
```

```bash
npm run build
php artisan filament:assets
php artisan vendor:publish --tag=filament-file-explorer-assets --force
```

After upgrading the package, republish assets and rebuild the Filament theme so CSS/JS and MIME icons stay in sync.

## Fast usage

### 1. Model trait

```php
use Ardavan\FilamentFileExplorer\Models\Concerns\HasFileExplorer;

class Project extends Model
{
    use HasFileExplorer;
}
```

Add `folder_id`:

```bash
php artisan filament-file-explorer:make-folder-migration projects
php artisan migrate
```

Root folder auto-creates on model `creating` when `folder_id` is empty.

### 2. Generate resource pages (from stubs)

```bash
php artisan filament-file-explorer:make-page ProjectResource
```

Creates thin pages that extend package bases. **Do not publish stubs** unless you want to change generated templates:

```bash
# optional — customize generators only
php artisan vendor:publish --tag=filament-file-explorer-stubs
```

Register pages:

```php
use Ardavan\FilamentFileExplorer\Resources\Concerns\HasFileExplorerResource;

class ProjectResource extends Resource
{
    use HasFileExplorerResource;

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
            ...static::getFileExplorerPages(
                Pages\ManageProjectFiles::class,
                Pages\ListProjectFiles::class,
            ),
        ];
    }
}
```

Generated explorer page:

```php
class ManageProjectFiles extends FileExplorerPage
{
    protected static string $resource = ProjectResource::class;
}
```

### 3. Authorizer (optional)

```bash
php artisan filament-file-explorer:make-authorizer
```

Bind in `AppServiceProvider` or config.

## Commands

| Command | Purpose |
|---------|---------|
| `filament-file-explorer:install` | Publish config (`--stubs`, `--migrate`) |
| `filament-file-explorer:make-page {Resource}` | Generate explorer + files list pages |
| `filament-file-explorer:make-folder-migration {table}` | Add `folder_id` FK |
| `filament-file-explorer:make-authorizer` | Generate authorizer class |

## Demo app

Local Filament v5 demo (path repo install):

```bash
# from a Laravel app with path repo ../filament-file-explorer
composer require ardavan/filament-file-explorer:@dev
php artisan filament-file-explorer:install --migrate
php artisan db:seed
```

Login: `admin@example.com` / `password`

See also [demo/README.md](demo/README.md) for the in-package QA notes.

## Form picker

```php
use Ardavan\FilamentFileExplorer\Forms\Components\FileExplorerPicker;

FileExplorerPicker::make('attachment_ids')
    ->scopeKey($record->fileExplorerScopeKey())
    ->rootFolderId($record->fileExplorerRootFolderId())
    ->multiple();
```

## Documentation

- [Installation](docs/installation.md)
- [Authorization](docs/authorization.md)
- [Explorer page](docs/explorer-page.md)
- [Files table](docs/files-table.md)
- [Form picker](docs/form-picker.md)
- [Configuration](docs/configuration.md)

## Development

```bash
composer install
./vendor/bin/pest
./vendor/bin/pint
```

Graphify output (`graphify-out/`) is gitignored — run locally:

```bash
graphify update .
```

## Translations

Shipped locales: **en**, **fa**, **ar**, **de**, **nl**, **fr**, **es**, **pt**, **tr**, **ru**, **zh_CN**, **ja**, **it**, **hi**, **id**.

Set your app locale (`config/app.php` or Filament panel locale). All UI strings use `filament-file-explorer::file-explorer.*`.

Publish to customize:

```bash
php artisan vendor:publish --tag=filament-file-explorer-translations
```

## License

MIT © [Ardavan Shamroshan](https://github.com/ardavanshamroshan)
