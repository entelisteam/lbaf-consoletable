<?php

declare(strict_types=1);

use EntelisTeam\Lbaf\ConsoleTable\Rector\Migration\Migration_20260610_1220_ConsoleTableFactoryMethods;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $builder = Migration_20260610_1220_ConsoleTableFactoryMethods::apply(RectorConfig::configure());
    $builder($rectorConfig);
};
