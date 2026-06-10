<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function render(array $map): string
{
    return ConsoleTable::fromKeyTitleArray($map);
}

function build(array $map)
{
    return ConsoleTable::fromKeyTitleArray($map, true);
}

-----
<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function render(array $map): string
{
    return ConsoleTable::fromMap($map);
}

function build(array $map)
{
    return ConsoleTable::fromMap($map, true);
}

