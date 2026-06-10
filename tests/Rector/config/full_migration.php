<?php

declare(strict_types=1);

use EntelisTeam\Lbaf\ConsoleTable\Rector\MigrationList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $builder = RectorConfig::configure();
    foreach (MigrationList::all() as $migration) {
        $builder = $migration::apply($builder);
    }
    $builder($rectorConfig);
};
