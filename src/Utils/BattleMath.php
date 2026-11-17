<?php

namespace App\Utils;

class BattleMath
{
    public static function sigma($x)
    {
        return ($x > 1 ? 2 - pow($x, -1.5) : pow($x, 1.5)) / 2;
    }

    public static function applyWallAndResidence($dp, $cdp, $def_wall, $def_tribe, $residence, $type)
    {
        $residenceBonus = (2 * ($residence * $residence)) + 10;

        if ($def_wall > 0) {
            $wallFactors = array(1 => 1.030, 2 => 1.020, 3 => 1.025, 6 => 1.015, 7 => 1.030, 8 => 1.028, 9 => 1.022);
            $factor = isset($wallFactors[$def_tribe]) ? $wallFactors[$def_tribe] : 1.025;

            $wallMultiplier = round(pow($factor, $def_wall), 3);

            if ($dp > 0 || $cdp > 0) {
                if ($type == 1) {
                    $dp *= $wallMultiplier;
                    $dp += 10;
                } else {
                    $dp *= $wallMultiplier;
                    $cdp *= $wallMultiplier;
                    $resBonus = $residenceBonus * $wallMultiplier;
                    $dp += $resBonus;
                    $cdp += $resBonus;
                }
            } else {
                $baseWall = 10 * $wallMultiplier * $def_wall;
                $dp = $baseWall;
                $cdp = $baseWall;

                if ($type != 1) {
                    $resBonus = $residenceBonus * $wallMultiplier;
                    $dp += $resBonus;
                    $cdp += $resBonus;
                } else {
                    $dp += 10;
                    $cdp = 0;
                }
            }
        } elseif ($type != 1) {
            $dp += $residenceBonus;
            $cdp += $residenceBonus;
        }

        return ['dp' => $dp, 'cdp' => $cdp];
    }

    public static function computeMorale($attpop, $defpop, $isWWVillage, $rap, $rdp)
    {
        if ($attpop > $defpop && !$isWWVillage) {
            $moralbonus = 1 / round(
                max(
                    0.667,
                    pow(
                        $defpop / $attpop,
                        0.2 * min(1, $rap / max($rdp, 1))
                    )
                ),
                3
            );
            return $moralbonus;
        }
        return 1.0;
    }

    public static function calculateCatapultsDamage(
        $catapultsQuantity,
        $catapultsUpgrade,
        $durability,
        $ADRatio,
        $strongerBuildings,
        $moraleBonus
    ) {
        $divider = $durability * $strongerBuildings;
        if ($divider <= 0) {
            $divider = 1;
        }
        $catapultsEfficiency = floor($catapultsQuantity / $divider);
        $sigma = self::sigma($ADRatio);
        return (
            4
            * $sigma
            * $catapultsEfficiency
            * $catapultsUpgrade
            / $moraleBonus
        );
    }
}
