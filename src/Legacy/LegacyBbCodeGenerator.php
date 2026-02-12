<?php

declare(strict_types=1);

namespace App\Legacy;

/**
 * @internal Adapter around legacy generator object
 */
interface LegacyGeneratorApi
{
    /** @return int|string */
    public function getMapCheck(int $wRef);
}

final class LegacyBbCodeGenerator implements BbCodeGenerator
{
    /** @var LegacyGeneratorApi */
    private object $legacyGenerator;

    public function __construct(mixed $legacyGenerator)
    {
        if (!is_object($legacyGenerator)) {
            throw new \InvalidArgumentException('Legacy generator must be an object');
        }
        /** @var LegacyGeneratorApi $legacyGenerator */
        $this->legacyGenerator = $legacyGenerator;
    }

    public function getMapCheck(int $wRef): int|string
    {
        $value = $this->legacyGenerator->getMapCheck($wRef);
        return $value;
    }
}
