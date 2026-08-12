<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Tests\Feature;

use Ardavan\FilamentFileExplorer\Models\Concerns\HasFileExplorer;
use Ardavan\FilamentFileExplorer\Models\Folder;
use Ardavan\FilamentFileExplorer\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HasFileExplorerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('demo_projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->foreignId('folder_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('demo_projects');

        parent::tearDown();
    }

    public function test_it_auto_creates_a_root_folder_when_the_model_uses_has_file_explorer(): void
    {
        $project = new class extends Model
        {
            use HasFileExplorer;

            protected $table = 'demo_projects';

            protected $guarded = [];

            public function fileExplorerScopeKeyPrefix(): string
            {
                return 'project';
            }
        };

        $project->fill(['name' => 'Acme', 'slug' => 'acme'])->save();

        $this->assertNotNull($project->folder_id);
        $this->assertNotNull(Folder::query()->find($project->folder_id));
        $this->assertSame('project.'.$project->id, $project->fileExplorerScopeKey());
    }

    public function test_it_does_not_overwrite_an_existing_folder_id(): void
    {
        $root = Folder::query()->create([
            'name' => 'Existing',
            'slug' => 'existing',
            'parent_id' => null,
        ]);

        $project = new class extends Model
        {
            use HasFileExplorer;

            protected $table = 'demo_projects';

            protected $guarded = [];
        };

        $project->fill([
            'name' => 'Acme',
            'slug' => 'acme',
            'folder_id' => $root->id,
        ])->save();

        $this->assertSame($root->id, $project->folder_id);
        $this->assertSame(1, Folder::query()->count());
    }
}
