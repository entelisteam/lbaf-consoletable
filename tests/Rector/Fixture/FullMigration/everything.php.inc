<?php

namespace App\Fixture;

use Lbaf\Helper\ConsoleTable;

class Report
{
    public function run(array $rows, array $map): void
    {
        echo ConsoleTable::fromArray($rows);
        echo ConsoleTable::from2dArray(['id', 'name'], $rows);
        echo ConsoleTable::fromKeyTitleArray($map, true);
    }
}

-----
<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

class Report
{
    public function run(array $rows, array $map): void
    {
        echo ConsoleTable::fromRows($rows);
        echo ConsoleTable::fromRows($rows, ['id', 'name']);
        // TODO(lbaf-consoletable): параметр $returnObject удалён, fromRows()/fromMap() всегда возвращают строку — перепишите вызов вручную
        echo ConsoleTable::fromMap($map, true);
    }
}
