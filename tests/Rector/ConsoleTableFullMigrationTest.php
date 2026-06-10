<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Сквозной тест всех миграций пакета из MigrationList:
 * downstream-код на старом Lbaf\Helper\ConsoleTable переводится
 * на новый класс, enum Align и новые фабрики за один прогон.
 */
final class ConsoleTableFullMigrationTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture/FullMigration');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/full_migration.php';
    }
}
