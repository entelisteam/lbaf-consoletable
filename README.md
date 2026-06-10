# entelisteam/lbaf-consoletable

Рендер ASCII-таблиц для вывода в консоль. Форк PEAR `Console_Table`, переработанный под PHP 8.2 и выделенный из фреймворка LBAF (бывший `Lbaf\Helper\ConsoleTable`).

- multibyte-safe (UTF-8, кириллица — ширина колонок считается корректно);
- ANSI-коды цвета не учитываются в ширине ячеек;
- многострочные ячейки и заголовки;
- выравнивание, фильтры колонок, строки итогов, разделители.

## Установка

```bash
composer require entelisteam/lbaf-consoletable
```

Требования: PHP ~8.2, ext-mbstring.

## Быстрый старт

### Таблица из списка записей — `fromRows()`

Заголовки берутся из ключей первой записи (записи — массивы или объекты):

```php
use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

echo ConsoleTable::fromRows([
    ['id' => 1, 'name' => 'foo'],
    ['id' => 2, 'name' => 'bar'],
]);
```

```
+----+------+
| id | name |
+----+------+
| 1  | foo  |
| 2  | bar  |
+----+------+
```

Заголовки можно задать явно — тогда записи могут быть простыми списками значений:

```php
echo ConsoleTable::fromRows([[1, 'foo'], [2, 'bar']], ['id', 'name']);
```

### Двухколоночная таблица из map — `fromMap()`

Каждая пара `ключ => значение` становится строкой, заголовки — `title` / `value`:

```php
echo ConsoleTable::fromMap(['host' => 'localhost', 'port' => 3306]);
```

```
+-------+-----------+
| title | value     |
+-------+-----------+
| host  | localhost |
| port  | 3306      |
+-------+-----------+
```

### Донастройка таблицы

Обе фабрики принимают последним аргументом `bool $returnObject` — при `true` возвращается объект `ConsoleTable` вместо готовой строки:

```php
use EntelisTeam\Lbaf\ConsoleTable\Align;
use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

$table = ConsoleTable::fromRows($rows, returnObject: true);
$table->setAlign(2, Align::Right);
echo $table->getTable(); // или просто echo $table — есть __toString()
```

## Объектный API

```php
use EntelisTeam\Lbaf\ConsoleTable\Align;
use EntelisTeam\Lbaf\ConsoleTable\ConsoleTable;

$table = new ConsoleTable(
    align: Align::Left,                    // выравнивание по умолчанию
    border: ConsoleTable::BORDER_ASCII,    // или свой символ рамки, '' — без рамки
    padding: 1,                            // пробелы вокруг содержимого ячейки
    charset: 'utf-8',
);

$table->setHeaders(['name', 'count']);
$table->addRow(['x', 2]);
$table->addRow(['y', 3.5]);
$table->addSeparator();                    // горизонтальный разделитель
$table->addRow(['z', 1]);

$table->setAlign(1, Align::Right);         // выравнивание конкретной колонки
$table->addFilter(0, strtoupper(...));     // callback к каждой ячейке колонки (заголовки не затрагивает)
$table->calculateTotalsFor([1]);           // строка итогов по колонке 1

echo $table->getTable();
```

```
+------+-------+
| name | count |
+------+-------+
| X    |     2 |
| Y    |   3.5 |
+------+-------+
| Z    |     1 |
+------+-------+
|      |   6.5 |
+------+-------+
```

Прочие методы:

- `addRow(array $row, bool $append = true)` — `false` добавляет строку в начало;
- `insertRow(array $row, int $rowId = 0)` — вставка перед строкой `$rowId`;
- `addCol(array $colData, int $colId = 0, int $rowId = 0)` — добавить колонку;
- `addData(array $data, int $colId = 0, int $rowId = 0)` — добавить двумерный массив; элемент `ConsoleTable::HORIZONTAL_RULE` вместо строки вставляет разделитель;
- `setCharset(string $charset)` — кодировка данных (по умолчанию `utf-8`).

Многострочные ячейки (`\n` внутри значения) автоматически разбиваются на несколько строк таблицы.

## Миграция с `Lbaf\Helper\ConsoleTable`

Пакет поставляет Rector-миграции (реестр — `EntelisTeam\Lbaf\ConsoleTable\Rector\MigrationList`, подхватывается автоматически через [entelisteam/lbaf-rector](https://github.com/entelisteam/lbaf-rector)):

| Миграция | Что делает |
|---|---|
| `Migration_..._ConsoleTableSplit` | `Lbaf\Helper\ConsoleTable` → `EntelisTeam\Lbaf\ConsoleTable\ConsoleTable` |
| `Migration_..._ConsoleTableAlignEnum` | константы `ConsoleTable::ALIGN_*` → enum `Align::Left/Center/Right` |
| `Migration_..._ConsoleTableFactoryMethods` | `fromArray()` → `fromRows()`, `from2dArray($h, $d)` → `fromRows($d, $h)`, `fromKeyTitleArray()` → `fromMap()` |

В downstream-проекте:

```bash
composer require --dev rector/rector entelisteam/lbaf-rector
vendor/bin/rector process --dry-run   # посмотреть изменения
vendor/bin/rector process             # применить
```

Не мигрируются автоматически (нужно править руками):

- выравнивание «сырым» числом вместо констант: `setAlign(0, 1)`;
- first-class callable (`ConsoleTable::fromArray(...)`) и spread-аргументы (`...$args`).

## Отличия от оригинала

Поведение рендеринга сохранено 1:1, но исправлены фаталы оригинала на PHP 8: `addSeparator()`, `calculateTotalsFor()`, `addCol()`, `addData()` и многострочные ячейки падали с `TypeError` (`count(null)` / `count(int)`).

## Разработка

```bash
composer install
composer test          # phpunit: тесты рендеринга + тесты rector-миграций
composer rector:check  # rector dry-run
```
