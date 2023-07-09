<?php

declare(strict_types=1);

namespace Rector\Services;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;

trait TraitInsertManipulatorTrait
{
    public function addAsFirstTrait(Class_ $class, string $traitName): void
    {
        $traitUse = new TraitUse([new FullyQualified($traitName)]);
        $this->addTraitUse($class, $traitUse);
    }

    private function addTraitUse(Class_ $class, TraitUse $traitUse): void
    {
        $beforeTraitTypes = [TraitUse::class, Property::class, ClassMethod::class];
        foreach ($beforeTraitTypes as $type) {
            foreach ($class->stmts as $key => $classStmt) {
                if (!$classStmt instanceof $type) {
                    continue;
                }
                $class->stmts = $this->insertBefore($class->stmts, $traitUse, $key);
                return;
            }
        }

        $class->stmts[] = $traitUse;
    }

    private function insertBefore(array $stmts, Stmt $stmt, int $key): array
    {
        array_splice($stmts, $key, 0, [$stmt]);
        return $stmts;
    }
}
