<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector\Migration;

use EntelisTeam\Lbaf\ConsoleTable\Rector\Rule\FlagConsoleTableReturnObjectRector;
use Rector\Configuration\RectorConfigBuilder;

/**
 * Миграция изменённого публичного API: параметр $returnObject удалён,
 * fromRows()/fromMap() всегда возвращают строку.
 *
 * Автоматически такие вызовы не переписываются — правило только помечает их
 * TODO-комментарием как требующие ручного рефакторинга.
 */
final class Migration_20260703_1400_ConsoleTableRemoveReturnObject
{
    /**
     * Применяет правила миграции к существующему конфигуратору.
     */
    public static function apply(RectorConfigBuilder $config): RectorConfigBuilder
    {
        return $config
            ->withRules([FlagConsoleTableReturnObjectRector::class]);
    }
}
