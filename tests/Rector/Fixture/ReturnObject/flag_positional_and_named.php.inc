<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function fixture(array $rows, array $map): void
{
    echo ConsoleTable::fromRows($rows, null, true);
    $table = ConsoleTable::fromMap($map, returnObject: true);
}

-----
<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

function fixture(array $rows, array $map): void
{
    // TODO(lbaf-consoletable): параметр $returnObject удалён, fromRows()/fromMap() всегда возвращают строку — перепишите вызов вручную
    echo ConsoleTable::fromRows($rows, null, true);
    // TODO(lbaf-consoletable): параметр $returnObject удалён, fromRows()/fromMap() всегда возвращают строку — перепишите вызов вручную
    $table = ConsoleTable::fromMap($map, returnObject: true);
}
