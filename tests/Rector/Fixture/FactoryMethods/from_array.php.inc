<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function render(array $rows): string
{
    return ConsoleTable::fromArray($rows);
}

function build(array $rows): ConsoleTable
{
    return ConsoleTable::fromArray($rows, true);
}

-----
<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function render(array $rows): string
{
    return ConsoleTable::fromRows($rows);
}

function build(array $rows): ConsoleTable
{
    return ConsoleTable::fromRows($rows, returnObject: true);
}

