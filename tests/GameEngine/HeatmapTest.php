<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Heatmap.php';

use Tests\DatabaseTestCase;

class HeatmapTest extends DatabaseTestCase
{
    public function testTribeNamesContainsAllSevenSupportedTribes()
    {
        $names = \Heatmap::tribeNames();
        $this->assertSame('Romans', $names[1]);
        $this->assertSame('Teutons', $names[2]);
        $this->assertSame('Gauls', $names[3]);
        $this->assertSame('Huns', $names[6]);
        $this->assertSame('Egyptians', $names[7]);
        $this->assertSame('Spartans', $names[8]);
        $this->assertSame('Vikings', $names[9]);
    }

    public function testGridClampsResolutionAndReturnsExpectedStructure()
    {
        $low = \Heatmap::grid(['res' => 2, 'inactive_days' => 0]);
        $this->assertEquals(\Heatmap::MIN_RES, $low['res']);
        $this->assertEquals(1, $low['inactive_days']);
        $this->assertArrayHasKey('cells', $low);
        $this->assertArrayHasKey('totals', $low);
        $this->assertArrayHasKey('tribe_totals', $low);

        $high = \Heatmap::grid(['res' => 999]);
        $this->assertEquals(\Heatmap::MAX_RES, $high['res']);
    }

    public function testGridAggregatesVillageCountsFromDatabase()
    {
        $conn = $GLOBALS['link'];
        mysqli_query($conn, "DELETE FROM " . TB_PREFIX . "vdata WHERE wref = 88001");
        mysqli_query($conn, "DELETE FROM " . TB_PREFIX . "wdata WHERE id = 88001");
        mysqli_query($conn, "DELETE FROM " . TB_PREFIX . "users WHERE id = 8801");
        mysqli_query($conn, "INSERT INTO " . TB_PREFIX . "users (id, username, email, tribe, timestamp) VALUES (8801, 'heatplayer', 'heat@example.com', 1, " . time() . ")");
        mysqli_query($conn, "INSERT INTO " . TB_PREFIX . "wdata (id, x, y, fieldtype, oasistype, occupied) VALUES (88001, 10, 10, 3, 0, 1)");
        mysqli_query($conn, "INSERT INTO " . TB_PREFIX . "vdata (wref, owner, name, capital, pop, cp, celebration, type, wood, clay, iron, crop, maxstore, maxcrop, loyalty, natar) VALUES (88001, 8801, 'HeatVil', 1, 120, 0, 0, 3, 0, 0, 0, 0, 800, 800, 100, 0)");

        $grid = \Heatmap::grid(['res' => 20]);
        $this->assertGreaterThanOrEqual(1, $grid['totals']['villages']);
        $this->assertGreaterThanOrEqual(1, $grid['tribe_totals'][1] ?? 0);
    }
}
