<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Commands;

use Ardavan\FilamentFileExplorer\Commands\Concerns\CopiesPackageStubs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePageCommand extends Command
{
    use CopiesPackageStubs;

    protected $signature = 'filament-file-explorer:make-page
                            {resource : Filament resource class basename or FQCN (e.g. ProjectResource)}
                            {--explorer : Generate explorer page only}
                            {--list : Generate files list page only}
                            {--force : Overwrite existing files}';

    protected $description = 'Generate File Explorer resource pages from stubs (publish stubs only to customize templates)';

    public function handle(): int
    {
        $resourceInput = (string) $this->argument('resource');
        $resourceClass = $this->resolveResourceClass($resourceInput);

        if ($resourceClass === null) {
            $this->components->error("Resource [{$resourceInput}] not found.");

            return self::FAILURE;
        }

        $wantExplorer = (bool) $this->option('explorer') || (! $this->option('explorer') && ! $this->option('list'));
        $wantList = (bool) $this->option('list') || (! $this->option('explorer') && ! $this->option('list'));

        // If only one flag passed, keep that one.
        if ($this->option('explorer') && ! $this->option('list')) {
            $wantList = false;
        }
        if ($this->option('list') && ! $this->option('explorer')) {
            $wantExplorer = false;
        }

        $resourceBasename = class_basename($resourceClass);
        $modelBasename = Str::beforeLast($resourceBasename, 'Resource') ?: $resourceBasename;
        $pagesNamespace = Str::beforeLast($resourceClass, '\\').'\\Pages';
        $pagesPath = $this->namespaceToPath($pagesNamespace);

        $explorerClass = 'Manage'.$modelBasename.'Files';
        $listClass = 'List'.$modelBasename.'Files';

        $created = [];

        if ($wantExplorer) {
            $path = $pagesPath.'/'.$explorerClass.'.php';
            if (! $this->option('force') && is_file($path)) {
                $this->components->warn("Skipped (exists): {$this->relativePath($path)}");
            } else {
                $this->copyPackageStub('ExplorerPage', $path, [
                    'namespace'      => $pagesNamespace,
                    'resource'       => $resourceClass,
                    'resourceClass'  => $resourceBasename,
                    'class'          => $explorerClass,
                ]);
                $created[] = $this->relativePath($path);
                $this->components->info("Created {$this->relativePath($path)}");
            }
        }

        if ($wantList) {
            $path = $pagesPath.'/'.$listClass.'.php';
            if (! $this->option('force') && is_file($path)) {
                $this->components->warn("Skipped (exists): {$this->relativePath($path)}");
            } else {
                $this->copyPackageStub('FilesListPage', $path, [
                    'namespace'      => $pagesNamespace,
                    'resource'       => $resourceClass,
                    'resourceClass'  => $resourceBasename,
                    'class'          => $listClass,
                ]);
                $created[] = $this->relativePath($path);
                $this->components->info("Created {$this->relativePath($path)}");
            }
        }

        $this->newLine();
        $this->line('Register in '.$resourceBasename.'::getPages():');
        $this->newLine();

        $lines = [];
        if ($wantExplorer) {
            $lines[] = "            'files' => Pages\\{$explorerClass}::route('/{record}/files'),";
        }
        if ($wantList) {
            $lines[] = "            'files-list' => Pages\\{$listClass}::route('/{record}/files-list'),";
        }

        $this->line(implode(PHP_EOL, $lines));
        $this->newLine();
        $this->line('Model must use HasFileExplorer. Optional resource helper:');
        $this->line('  use Ardavan\\FilamentFileExplorer\\Resources\\Concerns\\HasFileExplorerResource;');
        $this->newLine();
        $this->comment('Customize templates only if needed:');
        $this->comment('  php artisan vendor:publish --tag=filament-file-explorer-stubs');

        return self::SUCCESS;
    }

    protected function resolveResourceClass(string $input): ?string
    {
        $candidates = [];

        if (str_contains($input, '\\')) {
            $candidates[] = ltrim($input, '\\');
        } else {
            $basename = Str::finish(Str::studly($input), 'Resource');
            $basename = str_replace('ResourceResource', 'Resource', $basename);

            $candidates[] = 'App\\Filament\\Resources\\'.$basename;
            $candidates[] = 'App\\Filament\\Resources\\'.Str::studly($input);

            foreach (File::allFiles(app_path('Filament/Resources')) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                if ($file->getFilenameWithoutExtension() === $basename
                    || $file->getFilenameWithoutExtension() === Str::studly($input)) {
                    $relative = Str::of($file->getPathname())
                        ->after(app_path().DIRECTORY_SEPARATOR)
                        ->beforeLast('.php')
                        ->replace(DIRECTORY_SEPARATOR, '\\');
                    $candidates[] = 'App\\'.$relative;
                }
            }
        }

        foreach (array_unique($candidates) as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return null;
    }

    protected function namespaceToPath(string $namespace): string
    {
        $relative = Str::of($namespace)
            ->after('App\\')
            ->replace('\\', DIRECTORY_SEPARATOR)
            ->toString();

        return app_path($relative);
    }
}
