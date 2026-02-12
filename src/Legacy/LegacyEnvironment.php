<?php

declare(strict_types=1);

namespace App\Legacy;

final class LegacyEnvironment
{
    public static function defineTbPrefix(): void
    {
        if (defined('TB_PREFIX')) {
            return;
        }

        $prefix = getenv('TRAVIANZ_TB_PREFIX');
        if (!is_string($prefix) || $prefix === '') {
            $prefix = 's1_';
        }

        define('TB_PREFIX', $prefix);
    }
}
