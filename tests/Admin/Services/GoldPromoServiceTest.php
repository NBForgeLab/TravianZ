<?php
namespace Tests\Admin\Services;

use App\Admin\Services\GoldPromoService;
use Tests\DatabaseTestCase;

require_once __DIR__ . '/../../../GameEngine/GoldShop.php';

class GoldPromoServiceTest extends DatabaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (defined('TRAVIANZ_DB_AVAILABLE') && TRAVIANZ_DB_AVAILABLE) {
            \GoldShop::ensureSchema();
        }
    }

    public function testCreateToggleAndDeleteCode()
    {
        $db = $GLOBALS['database'];

        $service = new GoldPromoService($db);
        $code = 'PROMO' . random_int(1000, 9999);

        list($ok, $msg) = $service->createCode($code, 50, 5, 1, 0, 'test note', 1);
        $this->assertTrue($ok, $msg);

        $codes = \GoldShop::listCodes();
        $found = null;
        foreach ($codes as $row) {
            if ($row['code'] === strtoupper($code)) {
                $found = $row;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertEquals(50, (int)$found['gold']);
        $this->assertTrue($service->setActive((int)$found['id'], 0));
        $this->assertTrue($service->deleteCode((int)$found['id']));
    }
}
