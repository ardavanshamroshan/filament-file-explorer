# Installation

See [README](../README.md) for the full install flow.

## Steps

1. `composer require ardavan/filament-file-explorer:"^1.0" -W`
2. Publish Spatie media migrations
3. `php artisan filament-file-explorer:install`
4. `php artisan migrate`
5. Register `FilamentFileExplorerPlugin::make()` on your panel
6. Bind `FileExplorerAuthorizer` in your app

## Demo app

See [demo/README.md](../demo/README.md) for a local Filament panel with sample data.
