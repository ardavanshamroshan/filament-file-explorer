<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer;

use Ardavan\FilamentFileExplorer\Commands\InstallCommand;
use Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer;
use Ardavan\FilamentFileExplorer\Support\FileExplorerManager;
use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentFileExplorerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-file-explorer')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations()
            ->hasAssets(['resources/dist', 'resources/images'])
            ->hasCommand(InstallCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(FileExplorerManager::class);
        $this->app->alias(FileExplorerManager::class, 'filament-file-explorer');

        $this->app->singleton(FolderTree::class);

        $this->app->singleton(FileExplorerAuthorizer::class, function ($app) {
            $class = config('filament-file-explorer.authorizer');

            return $app->make($class);
        });
    }

    public function packageBooted(): void
    {
        Livewire::addNamespace(
            'filament-file-explorer',
            classNamespace: 'Ardavan\\FilamentFileExplorer\\Livewire',
        );

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        $config = config('filament-file-explorer.routes');

        Route::middleware($config['middleware'] ?? ['web', 'auth'])
            ->prefix($config['prefix'] ?? 'file-explorer/media')
            ->name($config['name'] ?? 'filament-file-explorer.media.')
            ->group(function (): void {
                Route::get('{scopeKey}/files/{media}', [\Ardavan\FilamentFileExplorer\Http\Controllers\MediaController::class, 'show'])
                    ->name('show');
                Route::get('{scopeKey}/files/{media}/zip', [\Ardavan\FilamentFileExplorer\Http\Controllers\MediaController::class, 'zipMedia'])
                    ->name('zip-media');
                Route::get('{scopeKey}/folders/{folder}/zip', [\Ardavan\FilamentFileExplorer\Http\Controllers\MediaController::class, 'zipFolder'])
                    ->name('zip-folder');
            });
    }
}
