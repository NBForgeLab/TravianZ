<?php
use PHPUnit\Framework\TestCase;
use GameEngine\Account;

class AccountTest extends TestCase {
    public function testSignupInvalidTribe() {
        $database = $this->createMock(\GameEngine\Database::class);
        $session = $this->createMock(\GameEngine\Session::class);
        $form = $this->createMock(\GameEngine\Form::class);
        $mailer = $this->createMock(\GameEngine\Mailer::class);
        $generator = $this->createMock(\GameEngine\Generator::class);
        $message = $this->createMock(\GameEngine\Message::class);

        // Simulate a POST request with invalid tribe
        $_POST['ft'] = 'a1';
        $_POST['vid'] = 99; // Invalid tribe
        $_POST['name'] = 'testuser';
        $_POST['pw'] = 'password123';
        $_POST['email'] = 'test@example.com';
        $_POST['agb'] = 'on';

        // Expectation: form should add an error for tribe
        $form->expects($this->once())
            ->method('addError')
            ->with('tribe', $this->anything());

        $account = new Account($database, $session, $form, $mailer, $generator, $message);
    }
}