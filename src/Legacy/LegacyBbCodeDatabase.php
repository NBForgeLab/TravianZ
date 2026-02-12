<?php

declare(strict_types=1);

namespace App\Legacy;

/**
 * @internal Adapter around legacy database object
 */
interface LegacyDatabaseApi
{
    public function getAllianceName(string $id): string;
    public function getUserField(int $id, string $field, int $mode): string;
    /**
     * @return array<string, mixed>
     */
    public function getNotice2(int $id, mixed $arg, bool $full): array;
    /** @return int|string */
    public function getVilWref(string $x, string $y);
    public function getVillageType(int $wRef): int;
    public function getVillageState(int $wRef): bool;
    public function getVillageField(int $wRef, string $field): string;
    /**
     * @return array<string, mixed>
     */
    public function getOasisInfo(int $wRef): array;
}

final class LegacyBbCodeDatabase implements BbCodeDatabase
{
    /** @var LegacyDatabaseApi */
    private object $legacyDatabase;

    public function __construct(mixed $legacyDatabase)
    {
        if (!is_object($legacyDatabase)) {
            throw new \InvalidArgumentException('Legacy database must be an object');
        }
        /** @var LegacyDatabaseApi $legacyDatabase */
        $this->legacyDatabase = $legacyDatabase;
    }

    public function getAllianceName(string $id): string
    {
        return $this->legacyDatabase->getAllianceName($id);
    }

    public function getUserField(int $id, string $field, int $mode): string
    {
        return $this->legacyDatabase->getUserField($id, $field, $mode);
    }

    public function getNotice2(int $id, mixed $arg, bool $full): array
    {
        return $this->legacyDatabase->getNotice2($id, $arg, $full);
    }

    public function getVilWref(string $x, string $y): int
    {
        $wRef = $this->legacyDatabase->getVilWref($x, $y);
        return is_int($wRef) ? $wRef : (int) $wRef;
    }

    public function getVillageType(int $wRef): int
    {
        return $this->legacyDatabase->getVillageType($wRef);
    }

    public function getVillageState(int $wRef): bool
    {
        return $this->legacyDatabase->getVillageState($wRef);
    }

    public function getVillageField(int $wRef, string $field): string
    {
        return $this->legacyDatabase->getVillageField($wRef, $field);
    }

    public function getOasisInfo(int $wRef): array
    {
        return $this->legacyDatabase->getOasisInfo($wRef);
    }
}
