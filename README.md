# Installation

### Add repository to composer.json
```json
{
  "type": "vcs",
  "url": "git@github.com:solitus0/rector-phpunit-withconsecutive.git"
}
```
### Install package
```bash
composer require --dev solitus0/rector-phpunit-withconsecutive
```

# Rector usage:
```php
$rectorConfig->rule(TransformWithConsecutiveToWithRector::class);

$rectorConfig->ruleWithConfiguration(InjectTraitBasedOnMethodRector::class, [
    'fqnClassName' => WithConsecutiveTrait::class,
    'methodName' => 'withConsecutive',
]);
```

# Copy Trait:
```php
<?php

declare(strict_types=1);

namespace Tests\Trait;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;

trait WithConsecutiveTrait
{
    public static function withConsecutive(array $firstCallArguments, array ...$consecutiveCallsArguments): iterable
    {
        foreach ($consecutiveCallsArguments as $consecutiveCallArguments) {
            self::assertSameSize(
                $firstCallArguments,
                $consecutiveCallArguments,
                'Each expected arguments list need to have the same size.'
            );
        }

        $allConsecutiveCallsArguments = [$firstCallArguments, ...$consecutiveCallsArguments];

        $numberOfArguments = count($firstCallArguments);
        $argumentList = [];
        for ($argumentPosition = 0; $argumentPosition < $numberOfArguments; $argumentPosition++) {
            $argumentList[$argumentPosition] = array_column($allConsecutiveCallsArguments, $argumentPosition);
        }

        $mockedMethodCall = 0;
        $callbackCall = 0;
        foreach ($argumentList as $index => $argument) {
            yield new Callback(
                static function (mixed $actualArgument) use (
                    $argumentList,
                    &$mockedMethodCall,
                    &$callbackCall,
                    $index,
                    $numberOfArguments
                ): bool {
                    $expected = $argumentList[$index][$mockedMethodCall] ?? null;

                    $callbackCall++;
                    $mockedMethodCall = (int)($callbackCall / $numberOfArguments);

                    if ($expected instanceof Constraint) {
                        self::assertThat($actualArgument, $expected);
                    } else {
                        self::assertEquals($expected, $actualArgument);
                    }

                    return true;
                },
            );
        }
    }
}

```