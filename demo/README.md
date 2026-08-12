# Demo app

Minimal Filament panel for manual QA and screenshots.

## Setup

From this package root:

```bash
composer install
cp demo/.env.example demo/.env
cd demo && composer install && php artisan migrate --seed
php artisan serve
```

Register `FilamentFileExplorerPlugin` in `demo/app/Providers/Filament/AdminPanelProvider.php`.

The demo includes:

- `Project` model with `folder_id`
- Explorer sub-page
- Files table sub-page
- Form with `FileExplorerPicker`

Replace SVG placeholders in `docs/images/` with PNG screenshots from the demo for Packagist / Filament directory submission.
