<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Tests;

use EntelisTeam\Lbaf\ConsoleTable\Align;
use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;
use PHPUnit\Framework\TestCase;

/**
 * Характеризационные тесты: эталонный вывод зафиксирован по оригинальному
 * Lbaf\Helper\ConsoleTable (сценарии, работавшие на PHP 8.2) и по новой
 * реализации (сценарии, падавшие в оригинале на PHP 8: разделители,
 * итоги, многострочные ячейки, addCol/addData).
 */
final class ConsoleTableTest extends TestCase
{
    private const SAMPLE = [
        ['id' => 1, 'name' => 'foo', 'time' => 0.123],
        ['id' => 2, 'name' => 'длинное значение', 'time' => 12.5],
        ['id' => 3, 'name' => 'bar', 'time' => 0],
    ];

    private static function table(string ...$lines): string
    {
        return implode("\r\n", $lines) . "\r\n";
    }

    public function testFromRows(): void
    {
        $expected = self::table(
            '+----+------------------+-------+',
            '| id | name             | time  |',
            '+----+------------------+-------+',
            '| 1  | foo              | 0.123 |',
            '| 2  | длинное значение | 12.5  |',
            '| 3  | bar              | 0     |',
            '+----+------------------+-------+',
        );

        self::assertSame($expected, ConsoleTable::fromRows(self::SAMPLE));
    }

    public function testFromRowsOfObjects(): void
    {
        $objects = array_map(static fn(array $row): object => (object)$row, self::SAMPLE);

        self::assertSame(ConsoleTable::fromRows(self::SAMPLE), ConsoleTable::fromRows($objects));
    }

    public function testFromRowsEmpty(): void
    {
        self::assertSame('', ConsoleTable::fromRows([]));
    }

    public function testFromRowsReturnObject(): void
    {
        $table = ConsoleTable::fromRows(self::SAMPLE, returnObject: true);

        self::assertInstanceOf(ConsoleTable::class, $table);
        self::assertSame(ConsoleTable::fromRows(self::SAMPLE), $table->getTable());
    }

    public function testToString(): void
    {
        $table = ConsoleTable::fromRows(self::SAMPLE, returnObject: true);

        self::assertSame($table->getTable(), (string)ConsoleTable::fromRows(self::SAMPLE, returnObject: true));
    }

    public function testFromMap(): void
    {
        $expected = self::table(
            '+-------+-----------+',
            '| title | value     |',
            '+-------+-----------+',
            '| host  | localhost |',
            '| порт  | 3306      |',
            '+-------+-----------+',
        );

        self::assertSame($expected, ConsoleTable::fromMap(['host' => 'localhost', 'порт' => 3306]));
    }

    public function testFromRowsWithExplicitHeaders(): void
    {
        $expected = self::table(
            '+---+---+',
            '| a | b |',
            '+---+---+',
            '| 1 | 2 |',
            '| 3 | 4 |',
            '+---+---+',
        );

        self::assertSame($expected, ConsoleTable::fromRows([[1, 2], [3, 4]], ['a', 'b']));
    }

    public function testSetAlign(): void
    {
        $table = ConsoleTable::fromRows(self::SAMPLE, returnObject: true);
        $table->setAlign(1, Align::Right);
        $table->setAlign(2, Align::Center);

        $expected = self::table(
            '+----+------------------+-------+',
            '| id |             name | time  |',
            '+----+------------------+-------+',
            '| 1  |              foo | 0.123 |',
            '| 2  | длинное значение | 12.5  |',
            '| 3  |              bar |   0   |',
            '+----+------------------+-------+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testCustomBorderAndPadding(): void
    {
        $table = new ConsoleTable(Align::Left, '*', 2);
        $table->setHeaders(['id', 'name']);
        $table->addRow([1, 'foo']);

        $expected = self::table(
            '*****************',
            '*  id  *  name  *',
            '*****************',
            '*  1   *  foo   *',
            '*****************',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testNoBorder(): void
    {
        $table = new ConsoleTable(Align::Left, '');
        $table->setHeaders(['id', 'name']);
        $table->addRow([1, 'foo']);

        $expected = self::table(
            ' id  name ',
            ' 1   foo  ',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testAnsiCodesDoNotAffectWidth(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['name', 'status']);
        $table->addRow(['job', "\033[32mOK\033[0m"]);
        $table->addRow(['other', 'FAILED']);

        $expected = self::table(
            '+-------+--------+',
            '| name  | status |',
            '+-------+--------+',
            "| job   | \033[32mOK\033[0m     |",
            '| other | FAILED |',
            '+-------+--------+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testRaggedRowsAreFilled(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['a', 'b', 'c']);
        $table->addRow([1]);
        $table->addRow([1, 2, 3]);

        $expected = self::table(
            '+---+---+---+',
            '| a | b | c |',
            '+---+---+---+',
            '| 1 |   |   |',
            '| 1 | 2 | 3 |',
            '+---+---+---+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testMultilineCellsAndHeaders(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['id', "multi\nheader"]);
        $table->addRow([1, "line1\nline2\nline3"]);
        $table->addRow([2, 'single']);

        $expected = self::table(
            '+----+--------+',
            '| id | multi  |',
            '|    | header |',
            '+----+--------+',
            '| 1  | line1  |',
            '|    | line2  |',
            '|    | line3  |',
            '| 2  | single |',
            '+----+--------+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testAddSeparator(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['a', 'b']);
        $table->addRow([1, 2]);
        $table->addSeparator();
        $table->addRow([3, 4]);

        $expected = self::table(
            '+---+---+',
            '| a | b |',
            '+---+---+',
            '| 1 | 2 |',
            '+---+---+',
            '| 3 | 4 |',
            '+---+---+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testCalculateTotals(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['name', 'count']);
        $table->addRow(['x', 2]);
        $table->addRow(['y', 3.5]);
        $table->calculateTotalsFor([1]);

        $expected = self::table(
            '+------+-------+',
            '| name | count |',
            '+------+-------+',
            '| x    | 2     |',
            '| y    | 3.5   |',
            '+------+-------+',
            '|      | 5.5   |',
            '+------+-------+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testAddDataWithHorizontalRule(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['a', 'b', 'c']);
        $table->addData([[1, 2], ConsoleTable::HORIZONTAL_RULE, [3, 4]]);

        $expected = self::table(
            '+---+---+---+',
            '| a | b | c |',
            '+---+---+---+',
            '| 1 | 2 |   |',
            '+---+---+---+',
            '| 3 | 4 |   |',
            '+---+---+---+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testAddCol(): void
    {
        $table = new ConsoleTable();
        $table->setHeaders(['a', 'b', 'c']);
        $table->addRow([1, 2]);
        $table->addRow([3, 4]);
        $table->addCol(['x', 'y'], 2);

        $expected = self::table(
            '+---+---+---+',
            '| a | b | c |',
            '+---+---+---+',
            '| 1 | 2 | x |',
            '| 3 | 4 | y |',
            '+---+---+---+',
        );

        self::assertSame($expected, $table->getTable());
    }

    public function testFilterPrependAndInsert(): void
    {
        $table = ConsoleTable::fromRows([['a' => 1, 'b' => 22]], returnObject: true);
        $table->addFilter(0, static fn($value): string => "[$value]");
        $table->addRow([0, 'first'], false);
        $table->insertRow([9, 'ins'], 1);

        $expected = self::table(
            '+-----+-------+',
            '| a   | b     |',
            '+-----+-------+',
            '| [0] | first |',
            '| [9] | ins   |',
            '| [1] | 22    |',
            '+-----+-------+',
        );

        self::assertSame($expected, $table->getTable());
    }
}
