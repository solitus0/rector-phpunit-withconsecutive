<?php

declare(strict_types=1);

namespace Rector\Rector;

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        TransformWithConsecutiveToWithRector::class,
    ]);
};
