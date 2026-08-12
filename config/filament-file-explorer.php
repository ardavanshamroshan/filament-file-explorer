<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Bind your own implementation of FileExplorerAuthorizer in the host app,
    | or override this class name in config after publishing.
    |
    */
    'authorizer' => Ardavan\FilamentFileExplorer\Authorizers\AllowAllAuthorizer::class,

    /*
    |--------------------------------------------------------------------------
    | Media collection
    |--------------------------------------------------------------------------
    */
    'collection' => 'file-explorer',

    /*
    |--------------------------------------------------------------------------
    | Auto-create root folder
    |--------------------------------------------------------------------------
    |
    | When a model uses HasFileExplorer, create a root folder on creating
    | if folder_id is empty.
    |
    */
    'auto_create_root' => true,

    /*
    |--------------------------------------------------------------------------
    | Legacy morph class
    |--------------------------------------------------------------------------
    |
    | Set when migrating from another folder/media implementation so existing
    | media.model_type values keep resolving correctly.
    |
    */
    'morph_class' => null,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => 'file-explorer/media',
        'middleware' => ['web', 'auth'],
        'name' => 'filament-file-explorer.media.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload rules
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_size_kb' => 51200,
        'allowed_extensions' => [
            'pdf', 'doc', 'docx', 'txt', 'png', 'jpg', 'jpeg', 'webp', 'zip', 'rar',
        ],
        'allowed_mime_types' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
            'image/png',
            'image/jpeg',
            'image/webp',
            'application/zip',
            'application/x-zip-compressed',
            'application/x-rar-compressed',
            'application/vnd.rar',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Folders
    |--------------------------------------------------------------------------
    */
    'folders' => [
        'max_depth' => 12,
        'table' => 'file_explorer_folders',
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire component
    |--------------------------------------------------------------------------
    */
    'livewire_component' => Ardavan\FilamentFileExplorer\Livewire\FileExplorer::class,

];
