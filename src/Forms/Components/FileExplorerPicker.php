<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Forms\Components;

use Filament\Forms\Components\Field;

class FileExplorerPicker extends Field
{
    protected string $view = 'filament-file-explorer::filament.forms.file-explorer-picker';

    protected int $rootFolderId = 0;

    protected string $scopeKey = 'picker';

    protected bool $multiple = false;

    public static function make(?string $name = null): static
    {
        return parent::make($name);
    }

    public function rootFolderId(int $id): static
    {
        $this->rootFolderId = $id;

        return $this;
    }

    public function scopeKey(string $key): static
    {
        $this->scopeKey = $key;

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->multiple = $condition;

        return $this;
    }

    public function getRootFolderId(): int
    {
        return $this->rootFolderId;
    }

    public function getScopeKey(): string
    {
        return $this->scopeKey;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }
}
