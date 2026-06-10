<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable;

/**
 * Выравнивание содержимого колонки.
 *
 * Замена int-констант ConsoleTable::ALIGN_LEFT / ALIGN_CENTER / ALIGN_RIGHT.
 */
enum Align
{
    case Left;
    case Center;
    case Right;
}
