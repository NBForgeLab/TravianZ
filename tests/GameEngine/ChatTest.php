<?php
namespace Tests\GameEngine;
require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../src/GameEngine/ChatService.php';

class ChatTest extends \Tests\DatabaseTestCase
{
    private $chatService;
    private $database;
    private $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = $GLOBALS['database'];
        $this->session = $GLOBALS['session'];
        
        // Ensure chat table exists
        $this->database->query("CREATE TABLE IF NOT EXISTS " . TB_PREFIX . "chat (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_user INT,
            name VARCHAR(255),
            alli VARCHAR(255),
            date INT,
            msg TEXT
        )");

        $this->chatService = new \App\GameEngine\ChatService($this->session, $this->database);
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
