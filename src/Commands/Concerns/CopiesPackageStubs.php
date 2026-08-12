<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Commands\Concerns;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;

trait CopiesPackageStubs
{
    /**
     * @param  array<string, string>  $replacements
     */
    protected function copyPackageStub(string $stub, string $targetPath, array $replacements = []): void
    {
        $filesystem = app(Filesystem::class);
        $contents = $filesystem->get($this->resolveStubPath($stub));

        foreach ($replacements as $key => $value) {
            $contents = str_replace('{{ '.$key.' }}', $value, $contents);
        }

        $filesystem->ensureDirectoryExists(dirname($targetPath));
        $filesystem->put($targetPath, $contents);
    }

    protected function resolveStubPath(string $stub): string
    {
        $published = base_path('stubs/filament-file-explorer/'.$stub.'.stub');

        if (is_file($published)) {
            return $published;
        }

        $reflection = new ReflectionClass($this);
        $packageStubs = Str::of($reflection->getFileName())
            ->beforeLast('Commands')
            ->append('../stubs')
            ->toString();

        return $packageStubs.'/'.$stub.'.stub';
    }

    protected function relativePath(string $path): string
    {
        return Str::of($path)->replace(base_path().DIRECTORY_SEPARATOR, '')->toString();
    }
}
