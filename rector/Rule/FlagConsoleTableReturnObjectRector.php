<?php

declare(strict_types=1);

namespace EntelisTeam\Lbaf\ConsoleTable\Rector\Rule;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;

/**
 * Помечает TODO-комментарием вызовы фабрик ConsoleTable, передающие
 * $returnObject: параметр удалён, fromRows()/fromMap() всегда возвращают
 * строку, поэтому такие места нужно переписать вручную.
 *
 * Ловит и старые имена методов (fromArray/from2dArray/fromKeyTitleArray),
 * и новые (fromRows/fromMap); аргумент — позиционный, именованный
 * (returnObject: ...) или spread (...$args, содержимое не проверить).
 */
final class FlagConsoleTableReturnObjectRector extends AbstractRector
{
    private const CLASSES = [
        'Lbaf\Helper\ConsoleTable',
        'EntelisTeam\Lbaf\ConsoleTable\ConsoleTable',
    ];

    /**
     * Позиция параметра $returnObject в каждой фабрике.
     */
    private const RETURN_OBJECT_POSITION = [
        'fromArray' => 1,
        'from2dArray' => 2,
        'fromKeyTitleArray' => 1,
        'fromRows' => 2,
        'fromMap' => 1,
    ];

    private const TODO = '// TODO(lbaf-consoletable): параметр $returnObject удалён,'
        . ' fromRows()/fromMap() всегда возвращают строку — перепишите вызов вручную';

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Stmt::class];
    }

    /**
     * @param Stmt $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->ownsReturnObjectCall($node)) {
            return null;
        }

        $comments = $node->getComments();
        foreach ($comments as $comment) {
            if (str_contains($comment->getText(), 'TODO(lbaf-consoletable)')) {
                return null;
            }
        }

        $comments[] = new Comment(self::TODO);
        $node->setAttribute(AttributeKey::COMMENTS, $comments);

        return $node;
    }

    /**
     * Ищет вызов с $returnObject в выражениях statement-а, не спускаясь
     * во вложенные statement-ы (у них будет свой комментарий).
     */
    private function ownsReturnObjectCall(Node $node): bool
    {
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            foreach (is_array($subNode) ? $subNode : [$subNode] as $child) {
                if (!$child instanceof Node || $child instanceof Stmt) {
                    continue;
                }

                if ($child instanceof StaticCall && $this->passesReturnObject($child)) {
                    return true;
                }

                if ($this->ownsReturnObjectCall($child)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function passesReturnObject(StaticCall $call): bool
    {
        if (!$call->name instanceof Identifier || !$call->class instanceof Name) {
            return false;
        }

        if (!in_array($this->getName($call->class), self::CLASSES, true)) {
            return false;
        }

        $position = self::RETURN_OBJECT_POSITION[$call->name->toString()] ?? null;
        if ($position === null || $call->isFirstClassCallable()) {
            return false;
        }

        foreach ($call->getArgs() as $index => $arg) {
            if ($arg->unpack
                || $arg->name?->toString() === 'returnObject'
                || ($arg->name === null && $index >= $position)
            ) {
                return true;
            }
        }

        return false;
    }
}
