<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector\Migration;

use Rector\Configuration\RectorConfigBuilder;
use Rector\Renaming\Rector\Name\RenameClassRector;

/**
 * Миграция для downstream-проектов: переход с Lbaf-овского namespace на отдельный пакет
 */
final class Migration_20260610_1200_ConsoleTableSplit
{
    /**
     * Применяет правила миграции к существующему конфигуратору.
     */
    public static function apply(RectorConfigBuilder $config): RectorConfigBuilder
    {
        return $config
            ->withConfiguredRule(RenameClassRector::class, [
                'Lbaf\Helper\ConsoleTable' => 'EntelisTeam\Lbaf\ConsoleTable\ConsoleTable',
            ])

            //импортируем короткие имена через use вместо FQN, удаляем устаревшие use на Lbaf-овские классы
            ->withImportNames(importNames: true, importDocBlockNames: true, importShortClasses: false, removeUnusedImports: true);
    }
}
