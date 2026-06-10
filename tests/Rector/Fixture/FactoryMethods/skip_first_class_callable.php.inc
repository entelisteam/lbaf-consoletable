<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function fixture(): callable
{
    return ConsoleTable::fromArray(...);
}
