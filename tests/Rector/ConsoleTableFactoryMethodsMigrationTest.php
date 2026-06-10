<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Tests\Rector;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * Тесты миграции фабричных методов:
 * fromArray/from2dArray -> fromRows, fromKeyTitleArray -> fromMap.
 */
final class ConsoleTableFactoryMethodsMigrationTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture/FactoryMethods');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/factory_methods.php';
    }
}
