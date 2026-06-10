<?php

namespace App\Fixture;

use Lbaf\Helper\ConsoleTable;

class Report
{
    public function run(array $rows, array $map): void
    {
        $table = new ConsoleTable(ConsoleTable::ALIGN_RIGHT);
        $table->setAlign(1, \Lbaf\Helper\ConsoleTable::ALIGN_CENTER);
        echo $table->getTable();

        echo ConsoleTable::fromArray($rows);
        echo ConsoleTable::from2dArray(['id', 'name'], $rows);
        echo ConsoleTable::fromKeyTitleArray($map, true)->getTable();
    }
}

-----
<?php

namespace App\Fixture;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;
use EntelisTeam\Lbaf\ConsoleTable\Align;

class Report
{
    public function run(array $rows, array $map): void
    {
        $table = new ConsoleTable(Align::Right);
        $table->setAlign(1, Align::Center);
        echo $table->getTable();

        echo ConsoleTable::fromRows($rows);
        echo ConsoleTable::fromRows($rows, ['id', 'name']);
        echo ConsoleTable::fromMap($map, true)->getTable();
    }
}

