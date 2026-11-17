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
    }

    public function testGetRankReturnsArray()
    {
        $this->ranking = new \Ranking();
        $this->assertIsArray($this->ranking->getRank());
    }
}
