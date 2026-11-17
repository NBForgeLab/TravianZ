<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\IpResolver;

class IpResolverTest extends TestCase
{
    public function testToBinary(): void
    {
        $this->assertEquals(inet_pton('127.0.0.1'), IpResolver::toBinary('127.0.0.1'));
        $this->assertNull(IpResolver::toBinary('invalid'));
    }

    public function testIpInCidr(): void
    {
        $this->assertTrue(IpResolver::ipInCidr('127.0.0.1', '127.0.0.1'));
        $this->assertTrue(IpResolver::ipInCidr('192.168.1.5', '192.168.1.0/24'));
        $this->assertFalse(IpResolver::ipInCidr('192.168.2.5', '192.168.1.0/24'));
    }
}
