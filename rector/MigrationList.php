<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector;

use EntelisTeam\Lbaf\ConsoleTable\Rector\Migration\Migration_20260610_1200_ConsoleTableSplit;
use EntelisTeam\Lbaf\ConsoleTable\Rector\Migration\Migration_20260610_1220_ConsoleTableFactoryMethods;
use EntelisTeam\Lbaf\ConsoleTable\Rector\Migration\Migration_20260703_1400_ConsoleTableRemoveReturnObject;
use EntelisTeam\Lbaf\Rector\RectorMigrationListInterface;

/**
 * Реестр Rector-миграций пакета entelisteam/lbaf-consoletable.
 */
final class MigrationList implements RectorMigrationListInterface
{
    /**
     * @return list<class-string>
     */
    public static function all(): array
    {
        return [
            Migration_20260610_1200_ConsoleTableSplit::class,
            Migration_20260610_1220_ConsoleTableFactoryMethods::class,
            Migration_20260703_1400_ConsoleTableRemoveReturnObject::class,
        ];
    }
}
