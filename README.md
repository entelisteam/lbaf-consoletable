# entelisteam/lbaf-consoletable

Рендер ASCII-таблиц для вывода в консоль. Выделен из фреймворка LBAF (бывший `Lbaf\Helper\ConsoleTable`); формат вывода совместим с PEAR `Console_Table`, реализация написана с нуля.

- multibyte-safe (UTF-8, кириллица — ширина колонок считается корректно);
- ANSI-коды цвета не учитываются в ширине ячеек;
- многострочные ячейки и заголовки;
- недостающие ячейки коротких строк дозаполняются пустыми.

## Установка

```bash
composer require entelisteam/lbaf-consoletable
```

Требования: PHP ~8.2, ext-mbstring.

## Использование

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

Обе фабрики возвращают готовую строку — это весь публичный API.

## Миграция с `Lbaf\Helper\ConsoleTable`

Пакет поставляет Rector-миграции (реестр — `EntelisTeam\Lbaf\ConsoleTable\Rector\MigrationList`, подхватывается автоматически через [entelisteam/lbaf-rector](https://github.com/entelisteam/lbaf-rector)):

| Миграция | Что делает |
|---|---|
| `Migration_..._ConsoleTableSplit` | `Lbaf\Helper\ConsoleTable` → `EntelisTeam\Lbaf\ConsoleTable\ConsoleTable` |
| `Migration_..._ConsoleTableFactoryMethods` | `fromArray()` → `fromRows()`, `from2dArray($h, $d)` → `fromRows($d, $h)`, `fromKeyTitleArray()` → `fromMap()` |
| `Migration_..._ConsoleTableRemoveReturnObject` | помечает TODO-комментарием вызовы, передававшие `$returnObject` — параметр удалён, нужен ручной рефакторинг |

В downstream-проекте:

```bash
composer require --dev rector/rector entelisteam/lbaf-rector
vendor/bin/rector process --dry-run   # посмотреть изменения
vendor/bin/rector process             # применить
```

**Внимание:** объектный API оригинала (`setHeaders`/`addRow`/`setAlign`/`getTable` и т.д.) в пакете отсутствует — миграции покрывают только вызовы фабрик. Код, использующий объектный API, нужно переводить на `fromRows()`/`fromMap()` вручную.

Также не мигрируются автоматически first-class callable (`ConsoleTable::fromArray(...)`) и spread-аргументы (`...$args`).

## Отличия от оригинала

Публичный API сокращён до фабрик `fromRows()`/`fromMap()`, возвращающих строку; формат вывода для этих сценариев сохранён 1:1.

## Разработка

```bash
composer install
composer test          # phpunit: тесты рендеринга + тесты rector-миграций
composer rector:check  # rector dry-run
```

## Версионирование

Все пакеты LBAF следуют [SemVer](https://semver.org):

- **Major (`1.x` → `2.0`)** — слом обратной совместимости публичного API. Каждое такое изменение сопровождается Rector-миграцией (см. [lbaf-rector](https://github.com/entelisteam/lbaf-rector)). Обновляется только вручную: поднять constraint в `composer.json` и выполнить `composer update`.
- **Minor (`1.2` → `1.3`)** — новая функциональность, обратная совместимость сохранена.
- **Patch (`1.2.0` → `1.2.1`)** — исправления без изменения публичного API.

Правило: **если изменение требует Rector-миграции — это major**, иначе minor или patch.

Зависимости на пакеты LBAF указываются через caret (`"entelisteam/lbaf-*": "^1.2"`): minor и patch подтягиваются обычным `composer update`, major автоматически не устанавливается. После обновления Rector-миграции применяются автоматически (хук `post-update-cmd`); если хук не настроен — выполните `composer rector:fix`.
