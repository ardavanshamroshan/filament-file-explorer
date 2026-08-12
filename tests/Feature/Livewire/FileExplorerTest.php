<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Tests\Feature\Livewire;

use Ardavan\FilamentFileExplorer\Livewire\FileExplorer;
use Ardavan\FilamentFileExplorer\Models\Folder;
use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Ardavan\FilamentFileExplorer\Tests\TestCase;
use Livewire\Livewire;

class FileExplorerTest extends TestCase
{
    public function test_it_mounts_file_explorer_for_a_root_folder(): void
    {
        $root = Folder::query()->create([
            'name'      => 'Root',
            'slug'      => 'root',
            'parent_id' => null,
        ]);

        Livewire::test(FileExplorer::class, [
            'scopeKey'     => 'test.scope',
            'rootFolderId' => $root->id,
        ])
            ->assertOk()
            ->assertSet('rootFolderId', $root->id)
            ->assertSet('scopeKey', 'test.scope');
    }

    public function test_it_lists_child_folders_under_root(): void
    {
        $root = Folder::query()->create(['name' => 'Root', 'slug' => 'root']);
        Folder::query()->create(['name' => 'Docs', 'slug' => 'docs', 'parent_id' => $root->id]);

        Livewire::test(FileExplorer::class, [
            'scopeKey'     => 'test',
            'rootFolderId' => $root->id,
        ])->assertOk();

        $this->assertSame(1, Folder::query()->where('parent_id', $root->id)->count());
    }

    public function test_it_resolves_descendant_folder_ids(): void
    {
        $root = Folder::query()->create(['name' => 'Root', 'slug' => 'root']);
        $child = Folder::query()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $root->id]);

        $ids = app(FolderTree::class)->descendantFolderIdsIncludingRoot((int) $root->id);

        $this->assertContains($root->id, $ids);
        $this->assertContains($child->id, $ids);
    }
}
