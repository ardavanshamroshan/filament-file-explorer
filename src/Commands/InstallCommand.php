<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'filament-file-explorer:install
                            {--force : Overwrite existing published files}
                            {--stubs : Also publish stubs (only needed to customize generators)}
                            {--migrate : Run migrations after install}';

    protected $description = 'Install Filament File Explorer (config; stubs optional)';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag'   => 'filament-file-explorer-config',
            '--force' => $this->option('force'),
        ]);

        if ($this->option('stubs')) {
            $this->call('vendor:publish', [
                '--tag'   => 'filament-file-explorer-stubs',
                '--force' => $this->option('force'),
            ]);
        }

        if ($this->option('migrate')) {
            $this->call('migrate', ['--no-interaction' => true]);
        }

        $this->components->info('Filament File Explorer installed.');
        $this->newLine();
        $this->line('Fast path:');
        $this->line('  1. php artisan vendor:publish --provider="Spatie\\MediaLibrary\\MediaLibraryServiceProvider" --tag="medialibrary-migrations"');
        $this->line('  2. php artisan migrate');
        $this->line('  3. Register plugin: ->plugin(\\Ardavan\\FilamentFileExplorer\\FilamentFileExplorerPlugin::make())');
        $this->line('  4. Model: use HasFileExplorer; + folder_id column');
        $this->line('     php artisan filament-file-explorer:make-folder-migration {table}');
        $this->line('  5. Pages: php artisan filament-file-explorer:make-page {Resource}');
        $this->newLine();
        $this->comment('Stubs stay in package. Publish only to customize generators:');
        $this->comment('  php artisan filament-file-explorer:install --stubs');
        $this->comment('  # or: php artisan vendor:publish --tag=filament-file-explorer-stubs');

        $this->hintPanelPlugin();

        return self::SUCCESS;
    }

    protected function hintPanelPlugin(): void
    {
        $provider = app_path('Providers/Filament/AdminPanelProvider.php');

        if (! is_file($provider)) {
            return;
        }

        $contents = File::get($provider);

        if (str_contains($contents, 'FilamentFileExplorerPlugin')) {
            $this->components->info('AdminPanelProvider already registers FilamentFileExplorerPlugin.');

            return;
        }

        $this->components->warn('Add FilamentFileExplorerPlugin::make() to AdminPanelProvider.');
    }
}
