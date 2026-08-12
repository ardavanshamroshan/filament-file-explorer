<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Tests;

use Ardavan\FilamentFileExplorer\FilamentFileExplorerServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Livewire\Livewire::addNamespace(
            'filament-file-explorer',
            classNamespace: 'Ardavan\\FilamentFileExplorer\\Livewire',
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            \Illuminate\Database\DatabaseServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            MediaLibraryServiceProvider::class,
            FilamentFileExplorerServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('filesystems.default', 'public');
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root'   => storage_path('app/public'),
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
