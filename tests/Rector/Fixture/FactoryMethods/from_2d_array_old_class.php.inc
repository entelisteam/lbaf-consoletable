<?php

namespace App\Fixture;

use Lbaf\Helper\ConsoleTable;

function render(array $rows): string
{
    return ConsoleTable::from2dArray(['id', 'name'], $rows);
}

function build(array $rows)
{
    return ConsoleTable::from2dArray(['id', 'name'], $rows, true);
}

-----
<?php

namespace App\Fixture;

use Lbaf\Helper\ConsoleTable;

function render(array $rows): string
{
    return ConsoleTable::fromRows($rows, ['id', 'name']);
}

function build(array $rows)
{
    return ConsoleTable::fromRows($rows, ['id', 'name'], true);
}

