<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class MarketTest extends TestCase
{
    private $db;
    private $market;
    private $village;
    private $building;
    private $savedGlobals;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedGlobals = [
            'database' => $GLOBALS['database'] ?? null,
            'building' => $GLOBALS['building'] ?? null,
            'village' => $GLOBALS['village'] ?? null,
            'session' => $GLOBALS['session'] ?? null,
            'generator' => $GLOBALS['generator'] ?? null,
            'form' => $GLOBALS['form'] ?? null,
            'bid17' => $GLOBALS['bid17'] ?? null,
            'bid28' => $GLOBALS['bid28'] ?? null,
        ];

        if (!defined('TRADER_CAPACITY')) define('TRADER_CAPACITY', 1);
        if (!defined('USER_ON_VACATION')) define('USER_ON_VACATION', 'User is on vacation');
        require_once __DIR__ . '/../../GameEngine/Lang/en.php';

        $this->db = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->db->method('getMovement')->willReturn([]);
        $this->db->method('totalMerchantUsed')->willReturn(0);
        $this->db->method('getMarket')->willReturn([]);
        $this->db->method('getVillageField')->willReturn(0);
        $this->db->method('getUserField')->willReturn(0);
        $this->db->method('getVillageByName')->willReturn(0);
        $this->db->method('getVillage')->willReturn([
            'wref' => 2,
            'owner' => 2,
            'natar' => 0,
            'wood' => 1000,
            'clay' => 1000,
            'iron' => 1000,
            'crop' => 1000,
        ]);

        $this->village = (object) [
            'wid' => 1,
            'resarray' => ['f17' => 1],
            'coor' => ['x' => 0, 'y' => 0],
            'awood' => 1000,
            'aclay' => 1000,
            'airon' => 1000,
            'acrop' => 1000,
        ];
        $this->building = new class {
            public function getTypeLevel($id) { return 1; }
        };

        $GLOBALS['database'] = $this->db;
        $GLOBALS['building'] = $this->building;
        $GLOBALS['village'] = $this->village;
        $GLOBALS['session'] = (object) ['uid' => 1, 'tribe' => 1, 'mchecker' => 'test', 'goldclub' => false];
        $GLOBALS['generator'] = new class {
            public function procDistanceTime($a, $b, $c, $d) { return 100; }
        };
        $GLOBALS['bid17'] = [1 => ['attri' => 1]];
        $GLOBALS['bid28'] = [1 => ['attri' => 100]];

        $this->market = new \Market($this->db, $this->village, $GLOBALS['session'], $this->building, [], $GLOBALS['generator']);
    }

    protected function tearDown(): void
    {
        foreach ($this->savedGlobals as $k => $v) {
            $GLOBALS[$k] = $v;
        }
        parent::tearDown();
    }

    public function testMerchantAvail()
    {
        $this->market->merchant = 5;
        $this->market->used = 2;

        $this->assertEquals(3, $this->market->merchantAvail());
    }

    public function testProcMarketSendResourceValidationError()
    {
        $GLOBALS['form'] = new class {
            public $errors = [];
            public function addError($a, $b) { $this->errors[] = $b; }
            public function getErrors() { return $this->errors; }
        };

        $post = [
            'ft' => 'mk1',
            'send3' => 4,
            'getwref' => 2,
            'r1' => 100,
            'r2' => 0,
            'r3' => 0,
            'r4' => 0,
        ];

        $this->market->procMarket($post);

        $this->assertNotEmpty($GLOBALS['form']->getErrors());
    }
}
