<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentFileExplorerPlugin implements Plugin
{
    protected ?string $authorizerClass = null;

    public function getId(): string
    {
        return 'filament-file-explorer';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function authorizer(string $class): static
    {
        $this->authorizerClass = $class;

        return $this;
    }

    public function getAuthorizerClass(): ?string
    {
        return $this->authorizerClass;
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        if ($this->authorizerClass) {
            config(['filament-file-explorer.authorizer' => $this->authorizerClass]);
        }
    }
}
