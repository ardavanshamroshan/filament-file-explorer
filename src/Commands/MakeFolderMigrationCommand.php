<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Commands;

use Ardavan\FilamentFileExplorer\Commands\Concerns\CopiesPackageStubs;
use Illuminate\Console\Command;

class MakeFolderMigrationCommand extends Command
{
    use CopiesPackageStubs;

    protected $signature = 'filament-file-explorer:make-folder-migration
                            {table : Table name (e.g. projects)}
                            {--column=folder_id : Foreign key column name}';

    protected $description = 'Generate a migration that adds folder_id to a model table';

    public function handle(): int
    {
        $table = (string) $this->argument('table');
        $column = (string) $this->option('column');
        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_add_{$column}_to_{$table}_table.php";
        $path = database_path('migrations/'.$filename);

        $this->copyPackageStub('FolderIdMigration', $path, [
            'table'  => $table,
            'column' => $column,
        ]);

        $this->components->info("Created {$this->relativePath($path)}");
        $this->line('Next: php artisan migrate');
        $this->line('Then on the Eloquent model: use HasFileExplorer;');

        return self::SUCCESS;
    }
}
