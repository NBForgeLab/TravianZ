<?php

declare(strict_types=1);

namespace App\Legacy;

interface BbCodeDatabase
{
    public function getAllianceName(string $id): string;

    public function getUserField(int $id, string $field, int $mode): string;

    /**
     * @return array<string, mixed>
     */
    public function getNotice2(int $id, mixed $arg, bool $full): array;

    public function getVilWref(string $x, string $y): int;

    public function getVillageType(int $wRef): int;

    public function getVillageState(int $wRef): bool;

    public function getVillageField(int $wRef, string $field): string;

    /**
     * @return array<string, mixed>
     */
    public function getOasisInfo(int $wRef): array;
}
