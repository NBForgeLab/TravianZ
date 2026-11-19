<?php
namespace Tests\GameEngine;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../GameEngine/Form.php';
require_once __DIR__ . '/../../GameEngine/Mailer.php';
require_once __DIR__ . '/../../GameEngine/Generator.php';
require_once __DIR__ . '/../../GameEngine/Message.php';
require_once __DIR__ . '/../../GameEngine/Account.php';
require_once __DIR__ . '/../../GameEngine/Alliance.php';
require_once __DIR__ . '/../../GameEngine/Artifacts.php';
require_once __DIR__ . '/../../GameEngine/Automation.php';
require_once __DIR__ . '/../../GameEngine/Battle.php';
require_once __DIR__ . '/../../GameEngine/Building.php';
require_once __DIR__ . '/../../GameEngine/GoldShop.php';
require_once __DIR__ . '/../../GameEngine/Heatmap.php';
require_once __DIR__ . '/../../GameEngine/HeroItems.php';
require_once __DIR__ . '/../../GameEngine/Profile.php';
require_once __DIR__ . '/../../GameEngine/Ranking.php';
require_once __DIR__ . '/../../GameEngine/Technology.php';
require_once __DIR__ . '/../../GameEngine/Units.php';

class CoreBackwardCompatibilityTest extends TestCase
{
    private $savedGlobals = [];
    private $savedPost;
    private $savedGet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedPost = $_POST;
        $this->savedGet = $_GET;
        $_POST = [];
        $_GET = [];

        foreach (['database', 'session', 'form', 'mailer', 'generator', 'message', 'village', 'technology', 'building', 'unitsbytype', 'bid14', 'bid18', 'bid34', 'bid35', 'cp1', 'link'] as $k) {
            $this->savedGlobals[$k] = $GLOBALS[$k] ?? null;
        }
    }

    protected function tearDown(): void
    {
        $_POST = $this->savedPost;
        $_GET = $this->savedGet;
        foreach ($this->savedGlobals as $k => $v) {
            $GLOBALS[$k] = $v;
        }
        parent::tearDown();
    }

    public function testZeroArgConstructorsAndStaticApiParity()
    {
        $db = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $db->method('getJobs')->willReturn([]);
        $db->method('query')->willReturn(false);

        $GLOBALS['database'] = $db;
        $GLOBALS['session'] = new \Session();
        $GLOBALS['form'] = new \Form([]);
        $GLOBALS['mailer'] = $this->createMock(\Mailer::class);
        $GLOBALS['generator'] = new \MyGenerator();
        $GLOBALS['message'] = new \Message();
        $GLOBALS['village'] = (object) ['wid' => 1, 'resarray' => [], 'natar' => 0, 'allcrop' => 0, 'pop' => 0, 'master' => 0, 'capital' => 0, 'unitarray' => []];
        $GLOBALS['technology'] = new \Technology();
        $GLOBALS['building'] = new \Building();
        $GLOBALS['unitsbytype'] = [];
        $GLOBALS['bid14'] = [1 => ['attri' => 110]];
        $GLOBALS['bid18'] = [];
        $GLOBALS['bid34'] = [];
        $GLOBALS['bid35'] = [];

        $this->assertInstanceOf(\Account::class, new \Account());
        $this->assertInstanceOf(\Alliance::class, new \Alliance());
        $this->assertInstanceOf(\Artifacts::class, new \Artifacts());
        $this->assertInstanceOf(\Automation::class, new \Automation());
        $this->assertInstanceOf(\Battle::class, new \Battle());
        $this->assertInstanceOf(\Building::class, new \Building());
        $this->assertInstanceOf(\Form::class, new \Form());
        $this->assertInstanceOf(\MyGenerator::class, new \MyGenerator());
        $this->assertInstanceOf(\HeroItems::class, new \HeroItems());
        $this->assertInstanceOf(\Profile::class, new \Profile());
        $this->assertInstanceOf(\Ranking::class, new \Ranking());
        $this->assertInstanceOf(\Technology::class, new \Technology());
        $this->assertInstanceOf(\Units::class, new \Units());
        $this->assertInstanceOf(\App\Entity\User::class, new \App\Entity\User(1));

        $this->assertSame('ABC', \GoldShop::normCode(' abc '));
        $this->assertIsArray(\Heatmap::tribeNames());
        $this->assertIsBool(\Alliance::canAct(['alliance' => 0, 'forum_perm' => 0, 'owner' => 1]));

        $rcStatic = new \ReflectionMethod(\Automation::class, 'updateMax');
        $this->assertTrue($rcStatic->isStatic());
    }

    public function testLazyGlobalResolutionWhenInstantiatedBeforeGlobalsExist()
    {
        // Simulate production include order where Battle, Ranking, Alliance, Units, Market
        // are constructed before $GLOBALS['session'], $GLOBALS['village'], $GLOBALS['form'], etc. exist
        unset(
            $GLOBALS['database'],
            $GLOBALS['session'],
            $GLOBALS['form'],
            $GLOBALS['village'],
            $GLOBALS['building'],
            $GLOBALS['unitsbytype']
        );

        $battle = new \Battle();
        $ranking = new \Ranking();
        $alliance = new \Alliance();
        $units = new \Units();

        // Populate globals after construction (as Session.php and Village.php do at runtime)
        $db = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $db->method('getAlliance')->willReturn(['id' => 1, 'name' => 'Ally']);
        $db->method('getAlliPermissions')->willReturn(['opt5' => 0]);

        $GLOBALS['database'] = $db;
        $GLOBALS['session'] = (object) ['uid' => 1, 'alliance' => 1, 'username' => 'tester', 'sharedForums' => []];
        $GLOBALS['form'] = new \Form([]);
        $GLOBALS['village'] = (object) ['wid' => 10, 'resarray' => [], 'natar' => 0];
        $GLOBALS['unitsbytype'] = ['cavalry' => [4, 5, 6], 'catapult' => [8], 'ram' => [7]];

        $this->assertSame($db, $battle->database);
        $this->assertSame([4, 5, 6], $battle->unitsbytype['cavalry']);
        $this->assertSame(10, $ranking->village->wid);

        $alliance->procAlliance(['a' => 0, 'o' => 0, 'd' => 0]);
        $this->assertSame('Ally', $alliance->allianceArray['name']);
        $this->assertSame(10, $units->village->wid);

        $this->assertFalse(class_exists('NonExistent\\MissingClassForAutoloaderTest'));
        $this->assertTrue(class_exists(\App\Automation\AutomationDataProvider::class));
        $this->assertTrue(class_exists(\App\Automation\AutomationVillageUpkeepService::class));
    }
}
