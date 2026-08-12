# Demo app

Use the standalone **file-explorer-demo** Laravel app (Filament v5) with a path repo to this package.

## Setup

```bash
cd ../file-explorer-demo   # or your own Laravel + Filament app

composer require ardavan/filament-file-explorer:@dev
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan filament-file-explorer:install --migrate
php artisan db:seed
php artisan serve
```

Register `FilamentFileExplorerPlugin::make()` in `AdminPanelProvider` (already done in file-explorer-demo).

## Login

| Field | Value |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

Seeded via `AdminUserSeeder`. Sample projects + files via `FileExplorerDemoSeeder`.

## What to check

- `Project` model uses `HasFileExplorer`
- Explorer sub-page (`ProjectFilesExplorer`)
- Files table sub-page (`ProjectFilesList`)
- `FileExplorerPicker` on forms (if enabled)

Replace SVG placeholders in `docs/images/` with PNG screenshots from the demo for Packagist / Filament directory submission.
