<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector\Migration;

use Rector\Configuration\RectorConfigBuilder;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

/**
 * Миграция изменённого публичного API: int-константы ConsoleTable::ALIGN_*
 * заменены enum-ом EntelisTeam\Lbaf\ConsoleTable\Align
 * (параметры $align конструктора и setAlign() теперь принимают Align).
 *
 * Внимание: вызовы, передававшие выравнивание «сырым» числом (например,
 * setAlign(0, 1) вместо setAlign(0, ConsoleTable::ALIGN_RIGHT)),
 * автоматически не мигрируются — их нужно поправить вручную.
 */
final class Migration_20260610_1211_ConsoleTableAlignEnum
{
    private const ALIGN_MAP = [
        'ALIGN_LEFT' => 'Left',
        'ALIGN_CENTER' => 'Center',
        'ALIGN_RIGHT' => 'Right',
    ];

    /**
     * Применяет правила миграции к существующему конфигуратору.
     */
    public static function apply(RectorConfigBuilder $config): RectorConfigBuilder
    {
        $renames = [];
        //старый класс — на случай, если переименование класса ещё не применено,
        //новый — если константы уже переехали вместе с классом
        foreach (['Lbaf\Helper\ConsoleTable', 'EntelisTeam\Lbaf\ConsoleTable\ConsoleTable'] as $class) {
            foreach (self::ALIGN_MAP as $constant => $case) {
                $renames[] = new RenameClassAndConstFetch(
                    $class,
                    $constant,
                    'EntelisTeam\Lbaf\ConsoleTable\Align',
                    $case
                );
            }
        }

        return $config
            ->withConfiguredRule(RenameClassConstFetchRector::class, $renames)

            //импортируем короткие имена через use вместо FQN
            ->withImportNames(importNames: true, importDocBlockNames: true, importShortClasses: false, removeUnusedImports: true);
    }
}
