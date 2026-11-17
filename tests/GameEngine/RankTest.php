<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Ranking.php';

use Tests\DatabaseTestCase;

class RankTest extends DatabaseTestCase
{
    private $ranking;

    protected function setUp(): void
    {
        parent::setUp();
        // Constants
        if (!defined('SHOW_NATARS')) define('SHOW_NATARS', false);
        if (!defined('INCLUDE_ADMIN')) define('INCLUDE_ADMIN', false);
        if (!defined('TB_PREFIX')) define('TB_PREFIX', 'tz_');

        // Clean up data
        $GLOBALS['database']->query("DELETE FROM " . TB_PREFIX . "users");
        $GLOBALS['database']->query("DELETE FROM " . TB_PREFIX . "vdata");
        $GLOBALS['database']->query("DELETE FROM " . TB_PREFIX . "alidata");
    }

    public function testGetRankReturnsArray()
    {
        $this->ranking = new \Ranking($GLOBALS['database']);
        $this->assertIsArray($this->ranking->getRank());
    }

    public function testGetUserRankReturnsZeroIfNotFound()
    {
        $this->ranking = new \Ranking($GLOBALS['database']);
        $this->assertEquals(0, $this->ranking->getUserRank(999));
    }
}
