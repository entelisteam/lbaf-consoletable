<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector\Migration;

use EntelisTeam\Lbaf\ConsoleTable\Rector\Rule\RenameConsoleTableFactoryCallsRector;
use Rector\Configuration\RectorConfigBuilder;

/**
 * Миграция изменённого публичного API: три статические фабрики объединены в две.
 *
 *   fromArray($data)         -> fromRows($rows) — заголовки из ключей первой записи
 *   from2dArray($h, $data)   -> fromRows($rows, $headers) — внимание, порядок аргументов поменялся
 *   fromKeyTitleArray($data) -> fromMap($map)
 *
 * Перестановку/именование аргументов делает кастомное правило
 * RenameConsoleTableFactoryCallsRector (см. его docblock про ограничения).
 */
final class Migration_20260610_1220_ConsoleTableFactoryMethods
{
    /**
     * Применяет правила миграции к существующему конфигуратору.
     */
    public static function apply(RectorConfigBuilder $config): RectorConfigBuilder
    {
        return $config
            ->withRules([RenameConsoleTableFactoryCallsRector::class])

            //импортируем короткие имена через use вместо FQN
            ->withImportNames(importNames: true, importDocBlockNames: true, importShortClasses: false, removeUnusedImports: true);
    }
}
