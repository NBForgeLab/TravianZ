<?php
use PHPUnit\Framework\TestCase;
use App\Database\IDbConnection;

require_once __DIR__ . '/../../GameEngine/Building.php';

class BuildingTest extends TestCase
{
    private $db;
    private $sess;
    private $vil;
    private $log;

    protected function setUp(): void
    {
        $this->db = new MockDb();
        $this->sess = new Session();
        $this->vil = $this->createMock(stdClass::class);
        $this->vil->wid = 1; // Need to set wid
        $this->log = $this->createMock(stdClass::class);
    }

    public function testConstructor()
    {
        $building = new Building($this->db, $this->sess, $this->vil, $this->log);
        $this->assertInstanceOf(Building::class, $building);
    }
}
