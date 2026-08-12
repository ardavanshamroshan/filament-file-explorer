<?php

declare(strict_types=1);

namespace Ardavan\FilamentFileExplorer\Support;

use Ardavan\FilamentFileExplorer\Models\Concerns\HasFileExplorer;
use Illuminate\Database\Eloquent\Model;
use LogicException;

final class HasFileExplorerModel
{
    public static function uses(mixed $record): bool
    {
        return $record instanceof Model
            && in_array(HasFileExplorer::class, class_uses_recursive($record), true);
    }

    /**
     * @return Model&HasFileExplorer
     */
    public static function assert(mixed $record): Model
    {
        if (! self::uses($record)) {
            throw new LogicException(
                'Model must use '.HasFileExplorer::class.' or override the File Explorer page methods.'
            );
        }

        /** @var Model&HasFileExplorer $record */
        return $record;
    }
}
