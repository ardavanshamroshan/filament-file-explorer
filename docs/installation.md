# Installation

```bash
composer require ardavan/filament-file-explorer:"^0.5" -W
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan filament-file-explorer:install --migrate
```

Register `FilamentFileExplorerPlugin::make()` on the panel.

## Fast path

1. Model: `use HasFileExplorer`
2. `php artisan filament-file-explorer:make-folder-migration {table}` → migrate
3. `php artisan filament-file-explorer:make-page {Resource}` → register `files` / `files-list` pages
4. Optional: `php artisan filament-file-explorer:make-authorizer`

## Stubs

Generators use package stubs by default.

**Publish stubs only when you want to change generated output:**

```bash
php artisan vendor:publish --tag=filament-file-explorer-stubs
# or
php artisan filament-file-explorer:install --stubs
```

Published path: `stubs/filament-file-explorer/`.

If you do not publish, package stubs stay the source of truth.
