<?php
use PHPUnit\Framework\TestCase;
use App\Database\IDbConnection;

require_once __DIR__ . '/../DatabaseTestCase.php';

interface MockableDb extends IDbConnection {
    public function getVillage($id);
    public function getResourceLevel($vid);
    public function getHero($uid);
}

class BattleTest extends \Tests\DatabaseTestCase
{
    private $database;
    private $session;
    private $form;
    private $village;
    private $unitsbytype;

    protected function setUp(): void
    {
        parent::setUp();
        global $database;
        $this->database = $database;
        $this->session = $this->createMock(Session::class);
        $this->form = $this->createMock(stdClass::class);
        $this->village = $this->createMock(stdClass::class);
        $this->unitsbytype = $this->createMock(stdClass::class);
    }

    public function testProcSimHandlesInvalidTribe()
    {
        $battle = new Battle($this->database, $this->session, $this->form, $this->village, $this->unitsbytype);
        
        // Invalid tribe ID (should not throw, just return)
        $post = ['a1_v' => 10, 'a2_v1' => 1];
        
        $result = $battle->procSim($post);
        $this->assertNull($result);
    }

    public function testProcSimHandlesMissingTarget()
    {
        $battle = new Battle($this->database, $this->session, $this->form, $this->village, $this->unitsbytype);
        
        // No target
        $post = ['a1_v' => 1];
        
        $result = $battle->procSim($post);
        $this->assertNull($result);
    }

    public function testGetBattleHeroThrowsErrorWithoutDependencies() {
        // Clear globals to force failure
        $originalDb = $GLOBALS['database'];
        unset($GLOBALS['database']);
        $battle = new Battle(null, null, null, null, null);

        // Reflection to call private getBattleHero
        $reflection = new ReflectionClass(Battle::class);
        $method = $reflection->getMethod('getBattleHero');
        $method->setAccessible(true);

        // When database is null, calling getHero on it will throw a TypeError (or Error)
        $this->expectException(Error::class);
        $method->invoke($battle, 1);
        
        $GLOBALS['database'] = $originalDb;
    }

    public function testUsesGlobalsIfArgumentsAreNull()
    {
        $GLOBALS['database'] = $this->database;
        
        $battle = new Battle(null, null, null, null);
        
        // Reflection to check private property
        $reflection = new ReflectionClass(Battle::class);
        $property = $reflection->getProperty('database');
        $property->setAccessible(true);
        
        $this->assertEquals($this->database, $property->getValue($battle));
    }

    public function testComputeBreweryAttackBonusUsesInjectedDatabase()
    {
        $mockDatabase = $this->createMock(MockableDb::class);
        $mockDatabase->expects($this->once())->method('getVillage')->willReturn(['festival' => time() + 100, 'wref' => 1]);
        $mockDatabase->expects($this->once())->method('getResourceLevel')->willReturn([]);

        $battle = new Battle($mockDatabase, null, null, null, null, null, null, null, null);

        $reflection = new ReflectionClass(Battle::class);
        $method = $reflection->getMethod('computeBreweryAttackBonus');
        $method->setAccessible(true);
        
        $this->assertEquals(0, $method->invoke($battle, 1, 2));
    }
}
