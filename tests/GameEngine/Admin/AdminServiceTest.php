<?php
namespace Tests\GameEngine\Admin;

require_once __DIR__ . '/../../DatabaseTestCase.php';

use App\GameEngine\Admin\AdminService;
use Tests\DatabaseTestCase;

class AdminServiceTest extends DatabaseTestCase
{
    public function testPunishReducesPopulationBuildingsClearsTroopsAndWarehouses()
    {
        $db = $GLOBALS['database'];
        $link = $GLOBALS['link'];
        $uid = 8701;
        $wid = 87001;

        mysqli_query($link, "DELETE FROM " . TB_PREFIX . "units WHERE vref = $wid");
        mysqli_query($link, "DELETE FROM " . TB_PREFIX . "fdata WHERE vref = $wid");
        mysqli_query($link, "DELETE FROM " . TB_PREFIX . "vdata WHERE wref = $wid");
        mysqli_query($link, "DELETE FROM " . TB_PREFIX . "users WHERE id = $uid");

        mysqli_query($link, "INSERT INTO " . TB_PREFIX . "users (id, username, email, tribe) VALUES ($uid, 'punishuser', 'p@example.com', 2)");
        mysqli_query($link, "INSERT INTO " . TB_PREFIX . "wdata (id, x, y, fieldtype, oasistype, occupied) VALUES ($wid, 20, 20, 3, 0, 1)");
        mysqli_query($link, "INSERT INTO " . TB_PREFIX . "vdata (wref, owner, name, capital, pop, cp, celebration, type, wood, clay, iron, crop, maxstore, maxcrop, loyalty, natar) VALUES ($wid, $uid, 'PunishVil', 1, 100, 0, 0, 3, 500, 500, 500, 500, 800, 800, 100, 0)");
        mysqli_query($link, "INSERT INTO " . TB_PREFIX . "fdata (vref, f19, f19t) VALUES ($wid, 10, 15)");
        mysqli_query($link, "INSERT INTO " . TB_PREFIX . "units (vref, u11, u12) VALUES ($wid, 50, 25)");

        $svc = new AdminService($db, 1);
        $svc->punish($uid, 50, true, true);

        $vRow = mysqli_fetch_assoc(mysqli_query($link, "SELECT pop, wood, clay, iron, crop FROM " . TB_PREFIX . "vdata WHERE wref = $wid"));
        $this->assertEquals(50, (int)$vRow['pop']);
        $this->assertEquals(0, (float)$vRow['wood']);
        $this->assertEquals(0, (float)$vRow['crop']);

        $fRow = mysqli_fetch_assoc(mysqli_query($link, "SELECT f19 FROM " . TB_PREFIX . "fdata WHERE vref = $wid"));
        $this->assertEquals(5, (int)$fRow['f19']);

        $uRow = mysqli_fetch_assoc(mysqli_query($link, "SELECT u11, u12 FROM " . TB_PREFIX . "units WHERE vref = $wid"));
        $this->assertEquals(0, (int)$uRow['u11']);
        $this->assertEquals(0, (int)$uRow['u12']);
    }
}
