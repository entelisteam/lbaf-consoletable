<?php

namespace App\Fixture;

use App\Other\ConsoleTable;

function render(array $rows): string
{
    return ConsoleTable::fromArray($rows);
}
