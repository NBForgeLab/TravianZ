<?php
namespace Tests\GameEngine;

use App\GameEngine\Config;
use App\Utils\Response;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigReadsDefinedConstantsAndReturnsNullForUndefined()
    {
        $cfg = new Config();
        $this->assertSame(SPEED, $cfg->get('SPEED'));
        $this->assertNull($cfg->get('TRAVIANZ_UNDEFINED_CONSTANT_XYZ_999'));
    }

    public function testResponseRedirectIsNoOpInTestMode()
    {
        $this->assertTrue(defined('TRAVIANZ_TEST_MODE') && TRAVIANZ_TEST_MODE);
        Response::redirect('dorf1.php');
        $this->addToAssertionCount(1);
    }
}
