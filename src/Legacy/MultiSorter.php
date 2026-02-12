<?php

declare(strict_types=1);

namespace App\Legacy;

final class MultiSorter
{
    /**
     * @param array<array<string, mixed>> $items
     * @param array<array{0: string, 1: bool, 2: int}> $criteria
     * @return array<array<string, mixed>>
     */
    public static function sort(array $items, array $criteria): array
    {
        usort(
            $items,
            static function (array $a, array $b) use ($criteria): int {
                foreach ($criteria as $criterion) {
                    $key = $criterion[0];
                    $order = $criterion[1];
                    $type = $criterion[2];

                    $av = $a[$key] ?? null;
                    $bv = $b[$key] ?? null;

                    $as = is_scalar($av) ? (string) $av : '';
                    $bs = is_scalar($bv) ? (string) $bv : '';
                    $af = is_numeric($av) ? (float) $av : 0.0;
                    $bf = is_numeric($bv) ? (float) $bv : 0.0;

                    switch ($type) {
                        case 1:
                            $result = strnatcasecmp($as, $bs);
                            break;
                        case 2:
                            $result = $af <=> $bf;
                            break;
                        case 3:
                            $result = strcmp($as, $bs);
                            break;
                        case 4:
                            $result = strcasecmp($as, $bs);
                            break;
                        default:
                            $result = strnatcmp($as, $bs);
                            break;
                    }

                    if ($result !== 0) {
                        return $result * ($order ? 1 : -1);
                    }
                }

                return 0;
            }
        );

        return $items;
    }
}
