<?php
declare(strict_types=1);

use App\Legacy\BbCodeRenderer;
use App\Legacy\BbCodeDatabase;
use App\Legacy\BbCodeGenerator;
use PHPUnit\Framework\TestCase;

final class BbCodeRendererTest extends TestCase
{
    public function testBasicFormattingTags(): void
    {
        $db = new class implements BbCodeDatabase {
            public function getAllianceName(string $id): string { return ''; }
            public function getUserField(int $id, string $field, int $x): string { return ''; }
            /** @return array<string, mixed> */
            public function getNotice2(int $id, mixed $a, bool $b): array { return []; }
            public function getVilWref(string $x, string $y): int { return 0; }
            public function getVillageType(int $wRef): int { return 0; }
            public function getVillageState(int $wRef): bool { return false; }
            public function getVillageField(int $wRef, string $field): string { return ''; }
            /** @return array<string, mixed> */
            public function getOasisInfo(int $wRef): array { return ['name' => '']; }
        };
        $gen = new class implements BbCodeGenerator {
            public function getMapCheck(int $wRef): int|string { return 0; }
        };

        $out = BbCodeRenderer::render('[b]x[/b] [i]y[/i] [u]z[/u]', $db, $gen);
        $this->assertSame('<b>x</b> <i>y</i> <u>z</u>', $out);
    }

    public function testPlayerPlaceholderTurnsIntoLink(): void
    {
        $db = new class implements BbCodeDatabase {
            public function getAllianceName(string $id): string { return ''; }
            public function getUserField(int $id, string $field, int $x): string { return 'Alice'; }
            /** @return array<string, mixed> */
            public function getNotice2(int $id, mixed $a, bool $b): array { return []; }
            public function getVilWref(string $x, string $y): int { return 0; }
            public function getVillageType(int $wRef): int { return 0; }
            public function getVillageState(int $wRef): bool { return false; }
            public function getVillageField(int $wRef, string $field): string { return ''; }
            /** @return array<string, mixed> */
            public function getOasisInfo(int $wRef): array { return ['name' => '']; }
        };
        $gen = new class implements BbCodeGenerator {
            public function getMapCheck(int $wRef): int|string { return 0; }
        };

        $out = BbCodeRenderer::render('[player]1[/player]', $db, $gen);
        $this->assertSame('<a href=spieler.php?uid=1>Alice</a>', $out);
    }
}
