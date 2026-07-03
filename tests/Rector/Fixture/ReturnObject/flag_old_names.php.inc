<?php

namespace App\Fixture;

use Lbaf\Helper\ConsoleTable;

function fixture(array $rows, array $map)
{
    return ConsoleTable::fromKeyTitleArray($map, true);
}

-----
<?php

namespace App\Fixture;

use Lbaf\Helper\ConsoleTable;

function fixture(array $rows, array $map)
{
    // TODO(lbaf-consoletable): параметр $returnObject удалён, fromRows()/fromMap() всегда возвращают строку — перепишите вызов вручную
    return ConsoleTable::fromKeyTitleArray($map, true);
}
