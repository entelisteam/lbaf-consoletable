<?php

declare(strict_types=1);

use EntelisTeam\Lbaf\ConsoleTable\Rector\Migration\Migration_20260703_1400_ConsoleTableRemoveReturnObject;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $builder = Migration_20260703_1400_ConsoleTableRemoveReturnObject::apply(RectorConfig::configure());
    $builder($rectorConfig);
};
