<?php
use PHPUnit\Framework\TestCase;
use App\Database\IDbConnection;

require_once __DIR__ . '/../../GameEngine/Units.php';

class UnitsTest extends TestCase
{
    private $database;
    private $form;
    private $generator;
    private $session;
    private $village;

    protected function setUp(): void
    {
        $this->database = new MockDb();
        $this->form = $this->createMock(stdClass::class);
        $this->generator = $this->createMock(stdClass::class);
        $this->session = $this->createMock(Session::class);
        $this->village = $this->createMock(stdClass::class);
    }

    public function testConstructor()
    {
        $units = new Units($this->database, $this->form, $this->generator, $this->session, $this->village);
        $this->assertInstanceOf(Units::class, $units);
    }
}
