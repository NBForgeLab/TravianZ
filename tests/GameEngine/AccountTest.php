<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../GameEngine/Form.php';
require_once __DIR__ . '/../../GameEngine/Mailer.php';
require_once __DIR__ . '/../../GameEngine/Generator.php';
require_once __DIR__ . '/../../GameEngine/Message.php';
require_once __DIR__ . '/../../GameEngine/Account.php';

class AccountTest extends TestCase {
    private $savedPost;
    private $savedGet;
    private $savedGlobals;

    protected function setUp(): void {
        parent::setUp();
        $this->savedPost = $_POST;
        $this->savedGet = $_GET;
        $this->savedGlobals = [
            'database' => $GLOBALS['database'] ?? null,
            'session' => $GLOBALS['session'] ?? null,
            'form' => $GLOBALS['form'] ?? null,
            'mailer' => $GLOBALS['mailer'] ?? null,
            'generator' => $GLOBALS['generator'] ?? null,
            'message' => $GLOBALS['message'] ?? null,
        ];
        if (!defined('USRNM_SPECIAL')) define('USRNM_SPECIAL', false);
        if (!defined('USRNM_EMPTY')) define('USRNM_EMPTY', 'USRNM_EMPTY');
        if (!defined('USRNM_SHORT')) define('USRNM_SHORT', 'USRNM_SHORT');
        if (!defined('USRNM_CHAR')) define('USRNM_CHAR', 'USRNM_CHAR');
        if (!defined('USRNM_TAKEN')) define('USRNM_TAKEN', 'USRNM_TAKEN');
        if (!defined('PW_EMPTY')) define('PW_EMPTY', 'PW_EMPTY');
        if (!defined('PW_SHORT')) define('PW_SHORT', 'PW_SHORT');
        if (!defined('PW_INSECURE')) define('PW_INSECURE', 'PW_INSECURE');
        if (!defined('EMAIL_EMPTY')) define('EMAIL_EMPTY', 'EMAIL_EMPTY');
        if (!defined('EMAIL_INVALID')) define('EMAIL_INVALID', 'EMAIL_INVALID');
        if (!defined('EMAIL_TAKEN')) define('EMAIL_TAKEN', 'EMAIL_TAKEN');
        if (!defined('TRIBE_EMPTY')) define('TRIBE_EMPTY', 'TRIBE_EMPTY');
        if (!defined('AGREE_ERROR')) define('AGREE_ERROR', 'AGREE_ERROR');
        if (!defined('WINNER_ERROR')) define('WINNER_ERROR', 'WINNER_ERROR');
    }

    protected function tearDown(): void {
        $_POST = $this->savedPost;
        $_GET = $this->savedGet;
        foreach ($this->savedGlobals as $k => $v) {
            $GLOBALS[$k] = $v;
        }
        parent::tearDown();
    }

    public function testSignupInvalidTribe() {
        $database = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $database->method('query')->willReturn(false);
        $database->method('query_new')->willReturn([['Total' => 0], ['Total' => 0]]);

        $session = $this->createMock(\Session::class);
        $form = new \Form([]);
        $mailer = $this->createMock(\Mailer::class);
        $generator = $this->createMock(\MyGenerator::class);
        $message = $this->createMock(\Message::class);

        $_POST = [
            'ft' => 'a1',
            'vid' => 99,
            'name' => 'testuser',
            'pw' => 'password123',
            'email' => 'test@example.com',
            'agb' => 'on',
        ];

        $account = new Account($database, $session, $form, $mailer, $generator, $message);

        $this->assertInstanceOf(Account::class, $account);
        $errors = $form->getErrors();
        $this->assertArrayHasKey('tribe', $errors);
    }

    public function testZeroArgConstructionIsBackwardCompatible() {
        $GLOBALS['database'] = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $GLOBALS['session'] = $this->createMock(\Session::class);
        $GLOBALS['form'] = new \Form([]);
        $GLOBALS['mailer'] = $this->createMock(\Mailer::class);
        $GLOBALS['generator'] = $this->createMock(\MyGenerator::class);
        $GLOBALS['message'] = $this->createMock(\Message::class);
        $_POST = [];
        $_GET = [];

        $account = new Account();
        $this->assertInstanceOf(Account::class, $account);
    }
}
