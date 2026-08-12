<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'filament-file-explorer:install
                            {--force : Overwrite existing files}';

    protected $description = 'Install Filament File Explorer (config, migrations, optional assets)';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag'   => 'filament-file-explorer-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag'   => 'filament-file-explorer-migrations',
            '--force' => $this->option('force'),
        ]);

        $this->components->info('Filament File Explorer installed.');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Publish Spatie Media Library migrations if needed:');
        $this->line('     php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"');
        $this->line('  2. Run migrations: php artisan migrate');
        $this->line('  3. Register the plugin in your panel provider:');
        $this->line('     ->plugin(\Ardavan\FilamentFileExplorer\FilamentFileExplorerPlugin::make())');
        $this->line('  4. Bind your authorizer in AppServiceProvider (optional for demos):');
        $this->line('     $this->app->singleton(\Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer::class, YourAuthorizer::class);');

        return self::SUCCESS;
    }
}
