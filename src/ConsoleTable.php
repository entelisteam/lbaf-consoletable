<?php
/**
 * Fork of PEAR Console_Table (via vladqa/console-table-php5), modernized for PHP 8.2.
 *
 * @see https://pear.php.net/package/ConsoleTable
 * @author Jan Schneider <jan@horde.org>
 * @author Kolesnikov Vladislav
 * @license http://www.debian.org/misc/bsd.license BSD License (3 Clause)
 */

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable;

use Stringable;

/**
 * Рендерит данные в виде ASCII-таблицы для вывода в консоль.
 */
final class ConsoleTable implements Stringable
{
    /**
     * Маркер горизонтального разделителя в данных таблицы.
     */
    public const HORIZONTAL_RULE = 1;

    /**
     * Стандартная ASCII-рамка: `|`, `-` и `+`.
     */
    public const BORDER_ASCII = -1;

    private const EOL = "\r\n";

    /**
     * Строки заголовка (многострочный заголовок разбивается на несколько строк).
     * @var list<array<int, mixed>>
     */
    private array $headers = [];

    /**
     * Данные таблицы; значение self::HORIZONTAL_RULE вместо строки — разделитель.
     * @var array<int, array<int, mixed>|int>
     */
    private array $data = [];

    private int $maxCols = 0;

    private int $maxRows = 0;

    /**
     * Максимальная ширина каждой колонки (вычисляется при генерации).
     * @var array<int, int>
     */
    private array $cellLengths = [];

    /**
     * Высота каждой строки в линиях; индекс -1 — строка заголовка.
     * @var array<int, int>
     */
    private array $rowHeights = [];

    /**
     * Фильтры колонок: пары [номер колонки, callback].
     * @var list<array{int, callable}>
     */
    private array $filters = [];

    /**
     * Номера колонок, по которым считается итоговая строка.
     * @var list<int>
     */
    private array $totalColumns = [];

    /**
     * Выравнивание каждой колонки.
     * @var array<int, Align>
     */
    private array $colAlign = [];

    private string $charset = 'utf-8';

    private readonly Align $defaultAlign;

    private readonly string|int $border;

    private readonly int $padding;

    /**
     * @param Align $align выравнивание колонок по умолчанию
     * @param string|int $border символ рамки или self::BORDER_ASCII
     * @param int $padding количество пробелов вокруг содержимого ячейки
     * @param string|null $charset кодировка данных (поддерживаемая mbstring)
     */
    public function __construct(
        Align $align = Align::Left,
        string|int $border = self::BORDER_ASCII,
        int $padding = 1,
        ?string $charset = null,
    ) {
        $this->defaultAlign = $align;
        $this->border = $border;
        $this->padding = $padding;

        if ($charset !== null && $charset !== '') {
            $this->setCharset($charset);
        }
    }

    /**
     * Задаёт кодировку данных таблицы.
     */
    public function setCharset(string $charset): void
    {
        $this->charset = strtolower($charset);
    }

    /**
     * Строит таблицу `title | value` из плоского массива key => value.
     *
     * @return ($returnObject is true ? self : string)
     */
    public static function fromKeyTitleArray(array $data, bool $returnObject = false): self|string
    {
        $rows = [];
        foreach ($data as $key => $value) {
            $rows[] = ['title' => $key, 'value' => $value];
        }

        return self::fromArray($rows, $returnObject);
    }

    /**
     * Строит таблицу из массива однотипных массивов/объектов;
     * заголовки берутся из ключей первого элемента.
     *
     * @return ($returnObject is true ? self : string)
     */
    public static function fromArray(array $data, bool $returnObject = false): self|string
    {
        $table = new self();

        if ($data !== []) {
            $table->setHeaders(array_keys((array)reset($data)));
            foreach ($data as $item) {
                $table->addRow((array)$item);
            }
        }

        return $returnObject ? $table : $table->getTable();
    }

    /**
     * Строит таблицу из заголовков и двумерного массива данных.
     *
     * @return ($returnObject is true ? self : string)
     */
    public static function from2dArray(array $headers, array $data, bool $returnObject = false): self|string
    {
        $table = new self();
        $table->setHeaders($headers);

        foreach ($data as $row) {
            $table->addRow($row);
        }

        return $returnObject ? $table : $table->getTable();
    }

    /**
     * Задаёт заголовки колонок.
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = [array_values($headers)];
        $this->updateRowsCols($headers);
    }

    /**
     * Добавляет строку в конец (или начало) таблицы.
     */
    public function addRow(array $row, bool $append = true): void
    {
        if ($append) {
            $this->data[] = array_values($row);
        } else {
            array_unshift($this->data, array_values($row));
        }

        $this->updateRowsCols($row);
    }

    /**
     * Вставляет строку перед строкой с номером $rowId.
     */
    public function insertRow(array $row, int $rowId = 0): void
    {
        array_splice($this->data, $rowId, 0, [$row]);

        $this->updateRowsCols($row);
    }

    /**
     * Добавляет колонку, начиная со строки $rowId.
     */
    public function addCol(array $colData, int $colId = 0, int $rowId = 0): void
    {
        foreach ($colData as $cell) {
            $this->data[$rowId++][$colId] = $cell;
        }

        $this->updateRowsCols();
        $this->maxCols = max($this->maxCols, $colId + 1);
    }

    /**
     * Добавляет двумерный массив данных, начиная с позиции ($rowId, $colId).
     * Элемент self::HORIZONTAL_RULE вместо строки вставляет разделитель.
     */
    public function addData(array $data, int $colId = 0, int $rowId = 0): void
    {
        foreach ($data as $row) {
            if ($row === self::HORIZONTAL_RULE) {
                $this->data[$rowId] = self::HORIZONTAL_RULE;
                $rowId++;
                continue;
            }

            $col = $colId;
            foreach ($row as $cell) {
                $this->data[$rowId][$col++] = $cell;
            }
            $this->updateRowsCols();
            $this->maxCols = max($this->maxCols, $col);
            $rowId++;
        }
    }

    /**
     * Добавляет горизонтальный разделитель.
     */
    public function addSeparator(): void
    {
        $this->data[] = self::HORIZONTAL_RULE;
    }

    /**
     * Добавляет фильтр колонки: callback применяется к каждой ячейке
     * перед генерацией таблицы, в порядке добавления фильтров.
     */
    public function addFilter(int $col, callable $callback): void
    {
        $this->filters[] = [$col, $callback];
    }

    /**
     * Задаёт выравнивание для колонки.
     */
    public function setAlign(int $colId, Align $align = Align::Left): void
    {
        $this->colAlign[$colId] = $align;
    }

    /**
     * Задаёт колонки, по которым в конец таблицы будет добавлена строка итогов.
     *
     * @param list<int> $cols номера колонок (с нуля)
     */
    public function calculateTotalsFor(array $cols): void
    {
        $this->totalColumns = $cols;
    }

    /**
     * Возвращает готовую таблицу.
     */
    public function getTable(): string
    {
        $this->applyFilters();
        $this->calculateTotals();
        $this->validateTable();

        return $this->buildTable();
    }

    public function __toString(): string
    {
        return $this->getTable();
    }

    /**
     * Применяет фильтры колонок к данным.
     */
    private function applyFilters(): void
    {
        foreach ($this->filters as [$column, $callback]) {
            foreach ($this->data as $rowId => $row) {
                if ($row !== self::HORIZONTAL_RULE) {
                    $this->data[$rowId][$column] = $callback($row[$column]);
                }
            }
        }
    }

    /**
     * Добавляет строку итогов по заданным колонкам.
     */
    private function calculateTotals(): void
    {
        if ($this->totalColumns === []) {
            return;
        }

        $this->addSeparator();

        $totals = [];
        foreach ($this->data as $row) {
            if (is_array($row)) {
                foreach ($this->totalColumns as $columnId) {
                    $totals[$columnId] = ($totals[$columnId] ?? 0) + $row[$columnId];
                }
            }
        }

        $this->data[] = $totals;
        $this->updateRowsCols();
    }

    /**
     * Пересчитывает количество строк/колонок и выравнивание новых колонок.
     */
    private function updateRowsCols(?array $rowData = null): void
    {
        $this->maxCols = max($this->maxCols, count($rowData ?? []));

        ksort($this->data);
        $keys = array_keys($this->data);
        $this->maxRows = ($keys === [] ? 0 : (int)end($keys)) + 1;

        for ($i = count($this->colAlign); $i < $this->maxCols; $i++) {
            $this->colAlign[$i] = $this->defaultAlign;
        }
    }

    /**
     * Выравнивает структуру таблицы: дозаполняет недостающие ячейки,
     * разбивает многострочные ячейки, считает ширины колонок.
     */
    private function validateTable(): void
    {
        if ($this->headers !== []) {
            $this->calculateRowHeight(-1, $this->headers[0]);
        }

        for ($i = 0; $i < $this->maxRows; $i++) {
            for ($j = 0; $j < $this->maxCols; $j++) {
                if (!isset($this->data[$i][$j]) &&
                    (!isset($this->data[$i]) || $this->data[$i] !== self::HORIZONTAL_RULE)
                ) {
                    $this->data[$i][$j] = '';
                }
            }

            $this->calculateRowHeight($i, $this->data[$i]);

            if ($this->data[$i] !== self::HORIZONTAL_RULE) {
                ksort($this->data[$i]);
            }
        }

        $this->splitMultilineRows();

        foreach ($this->headers as $headerRow) {
            $this->calculateCellLengths($headerRow);
        }
        for ($i = 0; $i < $this->maxRows; $i++) {
            $this->calculateCellLengths($this->data[$i]);
        }

        ksort($this->data);
    }

    /**
     * Вычисляет высоту строки в линиях; $rowNumber = -1 — строка заголовка.
     */
    private function calculateRowHeight(int $rowNumber, array|int $row): void
    {
        if (!isset($this->rowHeights[$rowNumber])) {
            $this->rowHeights[$rowNumber] = 1;
        }

        if (!is_array($row)) {
            return;
        }

        foreach ($row as $cell) {
            $lines = preg_split('/\r?\n|\r/', (string)$cell);
            $this->rowHeights[$rowNumber] = max($this->rowHeights[$rowNumber], count($lines));
        }
    }

    /**
     * Разбивает многострочные строки заголовка и данных на однострочные.
     */
    private function splitMultilineRows(): void
    {
        ksort($this->data);

        $headers = $this->splitSection($this->headers, count($this->headers), -1);
        if ($headers !== null) {
            $this->headers = $headers;
            $this->updateRowsCols();
        }

        $data = $this->splitSection($this->data, $this->maxRows, 0);
        if ($data !== null) {
            $this->data = $data;
            $this->updateRowsCols();
        }
    }

    /**
     * Разбивает многострочные строки секции (заголовок или данные)
     * на несколько однострочных «виртуальных» строк.
     *
     * @return array|null null — если многострочных строк не было
     */
    private function splitSection(array $rows, int $maxRows, int $heightOffset): ?array
    {
        $inserted = 0;
        $result = $rows;

        for ($i = 0; $i < $maxRows; $i++) {
            $height = $this->rowHeights[$i + $heightOffset];
            if ($height <= 1) {
                continue;
            }

            $split = [];
            for ($j = 0; $j < $this->maxCols; $j++) {
                $split[$j] = preg_split('/\r?\n|\r/', (string)($rows[$i][$j] ?? ''));
            }

            $virtualRows = [];
            for ($line = 0; $line < $height; $line++) {
                for ($j = 0; $j < $this->maxCols; $j++) {
                    $virtualRows[$line][$j] = $split[$j][$line] ?? '';
                }
            }

            array_splice($result, $i + $inserted, 1, $virtualRows);
            $inserted += count($virtualRows) - 1;
        }

        return $inserted > 0 ? $result : null;
    }

    /**
     * Обновляет максимальные ширины колонок по ячейкам строки.
     */
    private function calculateCellLengths(array|int $row): void
    {
        if (!is_array($row)) {
            return;
        }

        $count = count($row);
        for ($i = 0; $i < $count; $i++) {
            $this->cellLengths[$i] = max(
                $this->cellLengths[$i] ?? 0,
                $this->cellWidth((string)($row[$i] ?? ''))
            );
        }
    }

    /**
     * Видимая ширина строки: multibyte-длина без учёта ANSI-кодов цвета.
     */
    private function cellWidth(string $value): int
    {
        $value = (string)preg_replace('/\033\[[\d;]+m/', '', $value);

        return mb_strlen($value, $this->charset);
    }

    /**
     * Собирает таблицу в строку.
     */
    private function buildTable(): string
    {
        if ($this->data === []) {
            return '';
        }

        $rule = $this->border == self::BORDER_ASCII ? '|' : (string)$this->border;
        $separator = $this->getSeparator();

        $pad = str_repeat(' ', $this->padding);
        $rowBegin = $rule . $pad;
        $rowEnd = $pad . $rule;
        $implodeChar = $pad . $rule . $pad;

        $lines = [];
        foreach ($this->data as $row) {
            if ($row === self::HORIZONTAL_RULE) {
                if ($separator !== null) {
                    $lines[] = $separator;
                }
                continue;
            }

            foreach ($row as $j => $cell) {
                $cell = (string)$cell;
                if ($this->cellWidth($cell) < $this->cellLengths[$j]) {
                    $row[$j] = $this->pad($cell, $this->cellLengths[$j], $this->colAlign[$j]);
                }
            }
            $lines[] = $rowBegin . implode($implodeChar, $row) . $rowEnd;
        }

        $table = implode(self::EOL, $lines);
        if ($separator !== null) {
            $table = $separator . self::EOL . $table . self::EOL . $separator;
        }
        $table .= self::EOL;

        if ($this->headers !== []) {
            $table = $this->getHeaderLine() . self::EOL . $table;
        }

        return $table;
    }

    /**
     * Горизонтальный разделитель для шапки и краёв таблицы.
     */
    private function getSeparator(): ?string
    {
        if (!$this->border) {
            return null;
        }

        if ($this->border == self::BORDER_ASCII) {
            $rule = '-';
            $sect = '+';
        } else {
            $rule = $sect = (string)$this->border;
        }

        $cols = [];
        foreach ($this->cellLengths as $length) {
            $cols[] = str_repeat($rule, $length);
        }

        $pad = str_repeat($rule, $this->padding);

        return $sect . $pad . implode($pad . $sect . $pad, $cols) . $pad . $sect;
    }

    /**
     * Строки шапки таблицы (с верхним разделителем).
     */
    private function getHeaderLine(): string
    {
        foreach (array_keys($this->headers) as $j) {
            for ($i = 0; $i < $this->maxCols; $i++) {
                $cell = (string)($this->headers[$j][$i] ?? '');
                $this->headers[$j][$i] = $this->cellWidth($cell) < $this->cellLengths[$i]
                    ? $this->pad($cell, $this->cellLengths[$i], $this->colAlign[$i])
                    : $cell;
            }
        }

        $rule = $this->border == self::BORDER_ASCII ? '|' : (string)$this->border;
        $pad = str_repeat(' ', $this->padding);
        $implodeChar = $pad . $rule . $pad;

        $lines = [];
        $separator = $this->getSeparator();
        if ($separator !== null) {
            $lines[] = $separator;
        }
        foreach ($this->headers as $headerRow) {
            $lines[] = $rule . $pad . implode($implodeChar, $headerRow) . $pad . $rule;
        }

        return implode(self::EOL, $lines);
    }

    /**
     * Дополняет строку пробелами до видимой ширины $length (multibyte-safe,
     * ANSI-коды цвета не учитываются в ширине).
     */
    private function pad(string $input, int $length, Align $align): string
    {
        $diff = $length - $this->cellWidth($input);
        if ($diff <= 0) {
            return $input;
        }

        return match ($align) {
            Align::Right => str_repeat(' ', $diff) . $input,
            Align::Center => str_repeat(' ', intdiv($diff, 2)) . $input . str_repeat(' ', $diff - intdiv($diff, 2)),
            Align::Left => $input . str_repeat(' ', $diff),
        };
    }
}
