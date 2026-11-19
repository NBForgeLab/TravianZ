<?php
namespace Tests\GameEngine;
require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../src/GameEngine/ChatService.php';

class ChatTest extends \Tests\DatabaseTestCase
{
    private $chatService;
    private $database;
    private $session;
    private $savedSession;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (defined('TRAVIANZ_DB_AVAILABLE') && TRAVIANZ_DB_AVAILABLE && isset($GLOBALS['database'])) {
            $GLOBALS['database']->query("CREATE TABLE IF NOT EXISTS " . TB_PREFIX . "chat (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_user INT,
                name VARCHAR(255),
                alli VARCHAR(255),
                date INT,
                msg TEXT
            )");
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedSession = $GLOBALS['session'] ?? null;
        $this->database = $GLOBALS['database'];
        $this->session = clone $GLOBALS['session'];
        $this->chatService = new \App\GameEngine\ChatService($this->session, $this->database);
    }

    protected function tearDown(): void
    {
        $GLOBALS['session'] = $this->savedSession;
        parent::tearDown();
    }

    public function testAddAndGetData()
    {
        $this->session->username = "TestUser";
        $this->session->uid = 1;
        $this->session->alliance = "TestAlly";

        $this->chatService->addData("Hello, world!");

        $data = $this->chatService->getData();
        $this->assertStringContainsString("Hello, world!", $data);
        $this->assertStringContainsString("TestUser", $data);
    }
}
