<?php

declare(strict_types=1);

namespace Rector\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class TransformWithConsecutiveToWithRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Converts "->withConsecutive()" calls to "->with()" with array argument',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$builder->withConsecutive(
    [$case, NotificationRecipientTypes::CASE_JUDGE],
    [$case, NotificationRecipientTypes::CASE_PARTIES_WITH_ACCESS_PERMISSION],
    [$case, NotificationRecipientTypes::CASE_CREATOR]
);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$builder->with(
    ...self::withConsecutive(
        [$case, NotificationRecipientTypes::CASE_JUDGE],
        [$case, NotificationRecipientTypes::CASE_PARTIES_WITH_ACCESS_PERMISSION],
        [$case, NotificationRecipientTypes::CASE_CREATOR]
    )
);
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @inheritDoc
     */
    public function refactor(Node $node): ?Node
    {
        /** @var MethodCall $methodCall */
        $methodCall = $node;

        if ($this->isName($methodCall->name, 'withConsecutive')) {
            $args = $methodCall->args;

            $withConsecutive = $this->nodeFactory->createStaticCall(
                'self',
                'withConsecutive',
                [...$args]
            );

            $replacementArgs = $this->nodeFactory->createArg($withConsecutive);
            $replacementArgs->unpack = true;

            return $this->nodeFactory->createMethodCall(
                $methodCall->var,
                'with',
                [$replacementArgs]
            );
        }

        return null;
    }
}
