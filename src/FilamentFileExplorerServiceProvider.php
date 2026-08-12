<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer;

use Ardavan\FilamentFileExplorer\Commands\InstallCommand;
use Ardavan\FilamentFileExplorer\Commands\MakeAuthorizerCommand;
use Ardavan\FilamentFileExplorer\Commands\MakeFolderMigrationCommand;
use Ardavan\FilamentFileExplorer\Commands\MakePageCommand;
use Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer;
use Ardavan\FilamentFileExplorer\Livewire\FileExplorer;
use Ardavan\FilamentFileExplorer\Support\FileExplorerManager;
use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
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
            ->discoversMigrations()
            ->runsMigrations()
            ->hasAssets()
            ->hasCommands([
                InstallCommand::class,
                MakePageCommand::class,
                MakeAuthorizerCommand::class,
                MakeFolderMigrationCommand::class,
            ]);
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
        $this->registerFilamentAssets();

        Livewire::addNamespace(
            'filament-file-explorer',
            viewPath: __DIR__.'/../resources/views/livewire',
            classNamespace: 'Ardavan\\FilamentFileExplorer\\Livewire',
        );

        Livewire::component('filament-file-explorer::file-explorer', FileExplorer::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/filament-file-explorer'),
            ], 'filament-file-explorer-stubs');

            $this->publishes([
                __DIR__.'/../resources/images' => public_path('vendor/filament-file-explorer'),
            ], 'filament-file-explorer-assets');
        }

        $this->registerRoutes();
    }

    protected function registerFilamentAssets(): void
    {
        FilamentAsset::register([
            Css::make('filament-file-explorer', __DIR__.'/../resources/dist/file-explorer.css'),
            Js::make('filament-file-explorer', __DIR__.'/../resources/dist/file-explorer.js'),
        ], 'ardavan/filament-file-explorer');
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
