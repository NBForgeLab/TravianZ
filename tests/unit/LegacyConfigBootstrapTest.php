<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class LegacyConfigBootstrapTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testConfigBootstrapsWithoutFatalErrors(): void
    {
        require __DIR__ . '/../../GameEngine/config.php';

        $this->assertTrue(defined('SERVER_NAME'));
        $this->assertTrue(defined('LANG'));
        $this->assertTrue(defined('SPEED'));
        $this->assertTrue(defined('TIMEZONE'));
        $this->assertTrue(defined('TRZ_CSP_NONCE'));

        $this->assertIsString(SERVER_NAME);
        $this->assertIsString(LANG);
        $this->assertIsString(SPEED);
        $this->assertIsString(TIMEZONE);
        $this->assertIsString(TRZ_CSP_NONCE);

        $this->assertNotSame('', TRZ_CSP_NONCE);
    }
}

