<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Tests;

use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;
use PHPUnit\Framework\TestCase;

/**
 * Характеризационные тесты рендеринга: эталонный вывод зафиксирован
 * по оригинальному Lbaf\Helper\ConsoleTable.
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

    public function testFromRowsEmptyWithHeaders(): void
    {
        self::assertSame('', ConsoleTable::fromRows([], ['a', 'b']));
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

    public function testFromMapEmpty(): void
    {
        self::assertSame('', ConsoleTable::fromMap([]));
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

    public function testHeadersShorterThanRowsAreFilled(): void
    {
        $expected = self::table(
            '+---+---+---+',
            '| a | b |   |',
            '+---+---+---+',
            '| 1 | 2 | 3 |',
            '+---+---+---+',
        );

        self::assertSame($expected, ConsoleTable::fromRows([[1, 2, 3]], ['a', 'b']));
    }

    public function testAnsiCodesDoNotAffectWidth(): void
    {
        $rows = [
            ['name' => 'job', 'status' => "\033[32mOK\033[0m"],
            ['name' => 'other', 'status' => 'FAILED'],
        ];

        $expected = self::table(
            '+-------+--------+',
            '| name  | status |',
            '+-------+--------+',
            "| job   | \033[32mOK\033[0m     |",
            '| other | FAILED |',
            '+-------+--------+',
        );

        self::assertSame($expected, ConsoleTable::fromRows($rows));
    }

    public function testRaggedRowsAreFilled(): void
    {
        $expected = self::table(
            '+---+---+---+',
            '| a | b | c |',
            '+---+---+---+',
            '| 1 |   |   |',
            '| 1 | 2 | 3 |',
            '+---+---+---+',
        );

        self::assertSame($expected, ConsoleTable::fromRows([[1], [1, 2, 3]], ['a', 'b', 'c']));
    }

    public function testMultilineCellsAndHeaders(): void
    {
        $rows = [
            [1, "line1\nline2\nline3"],
            [2, 'single'],
        ];

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

        self::assertSame($expected, ConsoleTable::fromRows($rows, ['id', "multi\nheader"]));
    }
}
