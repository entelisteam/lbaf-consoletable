<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable;

/**
 * Рендерит список записей в ASCII-таблицу для вывода в консоль.
 *
 * Публичный API — фабрики fromRows() и fromMap(), обе возвращают готовую строку.
 *
 * - ширина колонок считается multibyte-safe (UTF-8);
 * - ANSI-коды цвета не учитываются в ширине ячеек;
 * - многострочные ячейки и заголовки разбиваются на несколько строк таблицы;
 * - недостающие ячейки коротких строк дозаполняются пустыми.
 */
final class ConsoleTable
{
    private const EOL = "\r\n";

    private function __construct()
    {
    }

    /**
     * Строит таблицу из списка однотипных записей (массивов или объектов).
     * Заголовки — $headers, а если не заданы — ключи первой записи.
     */
    public static function fromRows(array $rows, ?array $headers = null): string
    {
        if ($headers === null) {
            $headers = $rows === [] ? [] : array_keys((array)reset($rows));
        }

        $normalized = [];
        foreach ($rows as $row) {
            $normalized[] = self::stringifyCells((array)$row);
        }

        return self::render(self::stringifyCells($headers), $normalized);
    }

    /**
     * Строит двухколоночную таблицу `title | value` из плоского массива key => value.
     */
    public static function fromMap(array $map): string
    {
        $rows = [];
        foreach ($map as $title => $value) {
            $rows[] = ['title' => $title, 'value' => $value];
        }

        return self::fromRows($rows);
    }

    /**
     * @param list<string> $headers ячейки заголовка (пустой массив — таблица без шапки)
     * @param list<list<string>> $rows строки данных
     */
    private static function render(array $headers, array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $columnCount = count($headers);
        foreach ($rows as $row) {
            $columnCount = max($columnCount, count($row));
        }

        $headerLines = $headers === [] ? [] : self::explodeRow($headers, $columnCount);

        $bodyLines = [];
        foreach ($rows as $row) {
            foreach (self::explodeRow($row, $columnCount) as $line) {
                $bodyLines[] = $line;
            }
        }

        $widths = self::columnWidths([...$headerLines, ...$bodyLines], $columnCount);
        $separator = self::separatorLine($widths);

        $out = [$separator];
        foreach ($headerLines as $line) {
            $out[] = self::contentLine($line, $widths);
        }
        if ($headerLines !== []) {
            $out[] = $separator;
        }
        foreach ($bodyLines as $line) {
            $out[] = self::contentLine($line, $widths);
        }
        $out[] = $separator;

        return implode(self::EOL, $out) . self::EOL;
    }

    /**
     * Приводит все значения строки к строкам, отбрасывая ключи.
     *
     * @return list<string>
     */
    private static function stringifyCells(array $cells): array
    {
        return array_map(
            static fn(mixed $cell): string => (string)$cell,
            array_values($cells),
        );
    }

    /**
     * Разворачивает логическую строку в физические: многострочные ячейки
     * разбиваются по переводам строк, недостающие ячейки — пустые.
     *
     * @param list<string> $cells
     * @return list<list<string>>
     */
    private static function explodeRow(array $cells, int $columnCount): array
    {
        $cellLines = [];
        $height = 1;
        for ($col = 0; $col < $columnCount; $col++) {
            $lines = preg_split('/\r\n|\r|\n/', $cells[$col] ?? '');
            $cellLines[$col] = $lines;
            $height = max($height, count($lines));
        }

        $physical = [];
        for ($line = 0; $line < $height; $line++) {
            $row = [];
            for ($col = 0; $col < $columnCount; $col++) {
                $row[] = $cellLines[$col][$line] ?? '';
            }
            $physical[] = $row;
        }

        return $physical;
    }

    /**
     * Максимальная видимая ширина каждой колонки.
     *
     * @param list<list<string>> $lines
     * @return list<int>
     */
    private static function columnWidths(array $lines, int $columnCount): array
    {
        $widths = array_fill(0, $columnCount, 0);
        foreach ($lines as $line) {
            foreach ($line as $col => $cell) {
                $widths[$col] = max($widths[$col], self::visibleWidth($cell));
            }
        }

        return $widths;
    }

    /**
     * Видимая ширина ячейки: multibyte-длина без учёта ANSI-кодов цвета.
     */
    private static function visibleWidth(string $cell): int
    {
        return mb_strlen((string)preg_replace('/\e\[[\d;]*m/', '', $cell), 'UTF-8');
    }

    /**
     * Горизонтальный разделитель: +----+----+.
     *
     * @param list<int> $widths
     */
    private static function separatorLine(array $widths): string
    {
        $segments = array_map(
            static fn(int $width): string => str_repeat('-', $width + 2),
            $widths,
        );

        return '+' . implode('+', $segments) . '+';
    }

    /**
     * Строка таблицы: | ячейка | ячейка |, ячейки дополнены пробелами
     * до ширины колонки.
     *
     * @param list<string> $cells
     * @param list<int> $widths
     */
    private static function contentLine(array $cells, array $widths): string
    {
        $padded = [];
        foreach ($cells as $col => $cell) {
            $padded[] = $cell . str_repeat(' ', $widths[$col] - self::visibleWidth($cell));
        }

        return '| ' . implode(' | ', $padded) . ' |';
    }
}
