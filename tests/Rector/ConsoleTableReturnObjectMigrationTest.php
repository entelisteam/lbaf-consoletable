<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Тесты миграции удалённого параметра $returnObject:
 * вызовы, передававшие его, помечаются TODO-комментарием.
 */
final class ConsoleTableReturnObjectMigrationTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture/ReturnObject');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/return_object.php';
    }
}
