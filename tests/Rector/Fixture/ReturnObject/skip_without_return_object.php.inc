<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function fixture(array $rows, array $map): void
{
    echo ConsoleTable::fromRows($rows);
    echo ConsoleTable::fromRows($rows, ['id', 'name']);
    echo ConsoleTable::fromMap($map);
    $callable = ConsoleTable::fromRows(...);
}
