<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Http\Controllers;

use Ardavan\FilamentFileExplorer\Contracts\FileExplorerAuthorizer;
use Ardavan\FilamentFileExplorer\Models\Folder;
use Ardavan\FilamentFileExplorer\Support\FolderTree;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class MediaController extends Controller
{
    public function show(Request $request, string $scopeKey, Media $media): StreamedResponse
    {
        $this->authorizeMedia($scopeKey, $media);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response()->stream(function () use ($media): void {
            $stream = fopen($media->getPath(), 'rb');
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.$media->file_name.'"',
        ]);
    }

    public function zipMedia(Request $request, string $scopeKey, Media $media): StreamedResponse
    {
        $this->authorizeMedia($scopeKey, $media);

        $tmp = tempnam(sys_get_temp_dir(), 'fezip_');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $path = $media->getPath();
        if (is_file($path)) {
            $zip->addFile($path, $media->file_name);
        }
        $zip->close();

        $slug = Str::slug(pathinfo($media->file_name, PATHINFO_FILENAME) ?: 'file');
        $name = ($slug !== '' ? $slug : 'file').'.zip';

        return response()->streamDownload(function () use ($tmp): void {
            echo file_get_contents($tmp);
            @unlink($tmp);
        }, $name, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function zipFolder(Request $request, string $scopeKey, Folder $folder): StreamedResponse
    {
        $rootFolderId = (int) $request->integer('root');
        abort_unless($rootFolderId > 0, 400);

        $authorizer = app(FileExplorerAuthorizer::class);
        abort_unless($authorizer->canAccess($scopeKey, $rootFolderId), 403);
        abort_unless($authorizer->abilities($scopeKey, $rootFolderId)['download'] ?? false, 403);

        abort_unless(
            app(FolderTree::class)->isUnderRoot($folder, $rootFolderId),
            403
        );

        $tmp = tempnam(sys_get_temp_dir(), 'fezip_');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $this->addFolderToZip($zip, $folder, $folder->name ?: 'folder');
        $zip->close();

        $slug = Str::slug($folder->name ?: 'archive');
        $name = ($slug !== '' ? $slug : 'archive').'.zip';

        return response()->streamDownload(function () use ($tmp): void {
            echo file_get_contents($tmp);
            @unlink($tmp);
        }, $name, [
            'Content-Type' => 'application/zip',
        ]);
    }

    protected function authorizeMedia(string $scopeKey, Media $media): void
    {
        $folder = Folder::query()->find($media->model_id);
        abort_unless($folder instanceof Folder, 403);

        $rootFolderId = $this->resolveRootFolderId($scopeKey, $folder);
        $authorizer = app(FileExplorerAuthorizer::class);

        abort_unless($authorizer->canAccess($scopeKey, $rootFolderId), 403);
        abort_unless($authorizer->abilities($scopeKey, $rootFolderId)['download'] ?? false, 403);
        abort_unless(app(FolderTree::class)->isUnderRoot($folder, $rootFolderId), 403);
    }

    protected function resolveRootFolderId(string $scopeKey, Folder $folder): int
    {
        $current = $folder;

        while ($current->parent_id !== null) {
            $parent = Folder::query()->find($current->parent_id);
            if (! $parent) {
                break;
            }
            $current = $parent;
        }

        return (int) $current->id;
    }

    protected function addFolderToZip(ZipArchive $zip, Folder $folder, string $prefix): void
    {
        $collection = config('filament-file-explorer.collection', 'file-explorer');

        foreach ($folder->getMedia($collection) as $media) {
            $path = $media->getPath();
            if (is_file($path)) {
                $zip->addFile($path, trim($prefix.'/'.$media->file_name, '/'));
            }
        }

        foreach ($folder->children as $child) {
            $this->addFolderToZip($zip, $child, $prefix.'/'.$child->name);
        }
    }
}
