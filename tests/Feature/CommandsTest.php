<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Tests\Feature;

use Ardavan\FilamentFileExplorer\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class CommandsTest extends TestCase
{
    public function test_it_registers_make_commands(): void
    {
        $exitCode = Artisan::call('list', ['--raw' => true]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('filament-file-explorer:make-page', $output);
        $this->assertStringContainsString('filament-file-explorer:make-authorizer', $output);
        $this->assertStringContainsString('filament-file-explorer:make-folder-migration', $output);
    }

    public function test_it_publishes_stubs_only_when_requested(): void
    {
        $this->artisan('vendor:publish', [
            '--tag' => 'filament-file-explorer-stubs',
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists(base_path('stubs/filament-file-explorer/ExplorerPage.stub'));
        $this->assertFileExists(base_path('stubs/filament-file-explorer/FilesListPage.stub'));
        $this->assertFileExists(base_path('stubs/filament-file-explorer/Authorizer.stub'));
        $this->assertFileExists(base_path('stubs/filament-file-explorer/FolderIdMigration.stub'));
    }

    public function test_it_generates_a_folder_migration_from_stub(): void
    {
        $this->artisan('filament-file-explorer:make-folder-migration', [
            'table' => 'demo_widgets',
        ])->assertSuccessful();

        $files = glob(database_path('migrations/*_add_folder_id_to_demo_widgets_table.php')) ?: [];

        try {
            $this->assertNotEmpty($files);
            $contents = file_get_contents($files[0]);
            $this->assertStringContainsString("Schema::table('demo_widgets'", $contents);
            $this->assertStringContainsString("'folder_id'", $contents);
        } finally {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}
