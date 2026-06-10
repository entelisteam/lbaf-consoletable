<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function fixture(array $rows, array $headers, array $map): void
{
    ConsoleTable::fromArray(data: $rows, returnObject: true);
    ConsoleTable::from2dArray(headers: $headers, data: $rows);
    ConsoleTable::fromKeyTitleArray(data: $map);
}

-----
<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function fixture(array $rows, array $headers, array $map): void
{
    ConsoleTable::fromRows(rows: $rows, returnObject: true);
    ConsoleTable::fromRows($rows, $headers);
    ConsoleTable::fromMap(map: $map);
}

