<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector\Rule;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;

/**
 * Переписывает вызовы статических фабрик ConsoleTable на новый API:
 *
 *   fromArray($data)                  -> fromRows($data)
 *   fromArray($data, $bool)           -> fromRows($data, returnObject: $bool)
 *   from2dArray($headers, $data)      -> fromRows($data, $headers)
 *   from2dArray($headers, $data, $b)  -> fromRows($data, $headers, $b)
 *   fromKeyTitleArray($map)           -> fromMap($map)
 *   fromKeyTitleArray($map, $bool)    -> fromMap($map, $bool)
 *
 * Вызовы через first-class callable (ConsoleTable::fromArray(...)) и
 * spread-аргументы (...$args) не переписываются — их нужно поправить вручную.
 */
final class RenameConsoleTableFactoryCallsRector extends AbstractRector
{
    private const CLASSES = [
        'Lbaf\Helper\ConsoleTable',
        'EntelisTeam\Lbaf\ConsoleTable\ConsoleTable',
    ];

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->name instanceof Identifier || !$node->class instanceof Name) {
            return null;
        }

        if (!in_array($this->getName($node->class), self::CLASSES, true)) {
            return null;
        }

        if ($node->isFirstClassCallable() || $this->hasUnpackedArg($node)) {
            return null;
        }

        return match ($node->name->toString()) {
            'fromArray' => $this->refactorFromArray($node),
            'from2dArray' => $this->refactorFrom2dArray($node),
            'fromKeyTitleArray' => $this->refactorFromKeyTitleArray($node),
            default => null,
        };
    }

    /**
     * fromArray($data, $returnObject) -> fromRows($rows, $headers = null, $returnObject):
     * второй позиционный аргумент становится именованным returnObject.
     */
    private function refactorFromArray(StaticCall $call): StaticCall
    {
        $call->name = new Identifier('fromRows');

        foreach ($call->getArgs() as $position => $arg) {
            if ($arg->name?->toString() === 'data') {
                $arg->name = new Identifier('rows');
            } elseif ($arg->name === null && $position === 1) {
                $arg->name = new Identifier('returnObject');
            }
        }

        return $call;
    }

    /**
     * from2dArray($headers, $data, $returnObject) -> fromRows($rows, $headers, $returnObject):
     * первые два аргумента меняются местами.
     */
    private function refactorFrom2dArray(StaticCall $call): ?StaticCall
    {
        $headers = $rows = $returnObject = null;
        foreach ($call->getArgs() as $position => $arg) {
            $name = $arg->name?->toString();
            if ($name === 'headers' || ($name === null && $position === 0)) {
                $headers = $arg;
            } elseif ($name === 'data' || ($name === null && $position === 1)) {
                $rows = $arg;
            } elseif ($name === 'returnObject' || ($name === null && $position === 2)) {
                $returnObject = $arg;
            } else {
                return null;
            }
        }

        if ($headers === null || $rows === null) {
            return null;
        }

        $call->name = new Identifier('fromRows');

        $args = [new Arg($rows->value), new Arg($headers->value)];
        if ($returnObject !== null) {
            $args[] = new Arg(
                $returnObject->value,
                name: $returnObject->name === null ? null : new Identifier('returnObject')
            );
        }
        $call->args = $args;

        return $call;
    }

    /**
     * fromKeyTitleArray($data, $returnObject) -> fromMap($map, $returnObject):
     * сигнатура совпадает, меняется только имя (и имя параметра $data -> $map).
     */
    private function refactorFromKeyTitleArray(StaticCall $call): StaticCall
    {
        $call->name = new Identifier('fromMap');

        foreach ($call->getArgs() as $arg) {
            if ($arg->name?->toString() === 'data') {
                $arg->name = new Identifier('map');
            }
        }

        return $call;
    }

    private function hasUnpackedArg(StaticCall $call): bool
    {
        foreach ($call->getArgs() as $arg) {
            if ($arg->unpack) {
                return true;
            }
        }

        return false;
    }
}
