<?php
use PHPUnit\Framework\TestCase;
use App\Database\IDbConnection;

require_once __DIR__ . '/../../GameEngine/Units.php';
require_once __DIR__ . '/../DatabaseTestCase.php';

class UnitsTest extends \Tests\DatabaseTestCase
{
    private $database;
    private $form;
    private $generator;
    private $session;
    private $village;

    protected function setUp(): void
    {
        parent::setUp();
        global $database;
        $this->database = $database;
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
