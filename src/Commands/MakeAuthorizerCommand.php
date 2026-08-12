<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Commands;

use Ardavan\FilamentFileExplorer\Commands\Concerns\CopiesPackageStubs;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeAuthorizerCommand extends Command
{
    use CopiesPackageStubs;

    protected $signature = 'filament-file-explorer:make-authorizer
                            {name=FileExplorerAuthorizer : Authorizer class name}
                            {--force : Overwrite existing file}';

    protected $description = 'Generate a FileExplorerAuthorizer class from stub';

    public function handle(): int
    {
        $name = Str::studly((string) $this->argument('name'));
        $namespace = 'App\\Support';
        $path = app_path('Support/'.$name.'.php');

        if (! $this->option('force') && is_file($path)) {
            $this->components->error("Already exists: {$this->relativePath($path)}");

            return self::FAILURE;
        }

        $this->copyPackageStub('Authorizer', $path, [
            'namespace' => $namespace,
            'class'     => $name,
        ]);

        $this->components->info("Created {$this->relativePath($path)}");
        $this->newLine();
        $this->line('Bind in AppServiceProvider:');
        $this->line("  \$this->app->singleton(\\Ardavan\\FilamentFileExplorer\\Contracts\\FileExplorerAuthorizer::class, \\{$namespace}\\{$name}::class);");
        $this->newLine();
        $this->line('Or in config/filament-file-explorer.php:');
        $this->line("  'authorizer' => \\{$namespace}\\{$name}::class,");

        return self::SUCCESS;
    }
}
