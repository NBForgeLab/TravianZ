<?php

declare(strict_types=1);

namespace App\Legacy;

interface BbCodeGenerator
{
    public function getMapCheck(int $wRef): int|string;
}
