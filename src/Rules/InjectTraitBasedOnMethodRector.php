<?php

declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeFinder;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Services\TraitInsertManipulatorTrait;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class InjectTraitBasedOnMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    use TraitInsertManipulatorTrait;

    private string $fqnClassName;
    private string $methodName;

    public function getNodeTypes(): array
    {
        return [Node\Stmt\Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        /** @var Node\Stmt\Class_ $classNode */
        $classNode = $node;

        if ($this->doesClassUseTrait($classNode)) {
            return null;
        }

        $doesClassContainMethod = $this->doesClassContainMethodCall($classNode);
        if (!$doesClassContainMethod) {
            return null;
        }

        $this->addAsFirstTrait($classNode, $this->fqnClassName);

        return $classNode;
    }

    private function doesClassUseTrait(Class_ $classNode): bool
    {
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof TraitUse) {
                foreach ($stmt->traits as $trait) {
                    if ($trait->toString() === $this->fqnClassName) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function doesClassContainMethodCall(Class_ $classNode): bool
    {
        $nodeFinder = new NodeFinder();
        $methodCalls = $nodeFinder->findInstanceOf($classNode, Node\Expr\MethodCall::class);
        $staticMethodCalls = $nodeFinder->findInstanceOf($classNode, Node\Expr\StaticCall::class);
        $allMethodCalls = array_merge($methodCalls, $staticMethodCalls);

        foreach ($allMethodCalls as $methodCall) {
            /** @var Node\Expr\MethodCall $methodCall */
            if ($this->isName($methodCall->name, $this->methodName)) {
                return true;
            }
        }

        return false;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Injects a specific trait into a class if the class contains a specific method',
            [
                new RuleDefinition\CodeSample(
                    <<<'CODE_SAMPLE'
class MyClass
{
    public function myMethod()
    {
        // ...
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class MyClass
{
    use MyTrait;

    public function myMethod()
    {
        // ...
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function configure(array $configuration): void
    {
        if (!isset($configuration['fqnClassName'])) {
            throw new \InvalidArgumentException(
                sprintf('Required configuration option "fqnClassName" is missing for "%s".', self::class)
            );
        }

        if (!isset($configuration['methodName'])) {
            throw new \InvalidArgumentException(
                sprintf('Required configuration option "methodName" is missing for "%s".', self::class)
            );
        }

        $this->fqnClassName = $configuration['fqnClassName'];
        $this->methodName = $configuration['methodName'];
    }
}
