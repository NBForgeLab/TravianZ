<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPhpVersion(80400)
    ->withPaths([
        __DIR__ . '/src'
    ])
    ->withSets([
        SetList::PHP_84
    ]);
