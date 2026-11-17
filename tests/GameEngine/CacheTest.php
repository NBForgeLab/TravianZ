<?php
use PHPUnit\Framework\TestCase;
use App\GameEngine\Cache;

require_once __DIR__ . '/../../vendor/autoload.php';

class CacheTest extends TestCase
{
    public function testCache()
    {
        $cache = new Cache();
        $cache->set('key', 'value');
        $this->assertEquals('value', $cache->get('key'));
        $this->assertTrue($cache->has('key'));
        $cache->forget('key');
        $this->assertFalse($cache->has('key'));
    }
}
