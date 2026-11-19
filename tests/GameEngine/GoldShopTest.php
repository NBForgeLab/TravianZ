<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/GoldShop.php';

use Tests\DatabaseTestCase;

class GoldShopTest extends DatabaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        if (defined('TRAVIANZ_DB_AVAILABLE') && TRAVIANZ_DB_AVAILABLE) {
            \GoldShop::ensureSchema();
        }
    }

    public function testNormCodeNormalizesAndFiltersInvalidCharacters()
    {
        $this->assertSame('ABC-123_X.Y', \GoldShop::normCode('  abc-123_x.y!@#  '));
        $this->assertSame('', \GoldShop::normCode('   '));
    }

    public function testCreateCodeValidationAndRedeemLifecycle()
    {
        list($badLen, ) = \GoldShop::createCode('ab', 50, 0, 1, 0, '', 1);
        $this->assertFalse($badLen);

        list($badGold, ) = \GoldShop::createCode('VALID1', 0, 0, 1, 0, '', 1);
        $this->assertFalse($badGold);

        $conn = $GLOBALS['link'];
        mysqli_query($conn, "DELETE FROM " . TB_PREFIX . "users WHERE id = 8901");
        mysqli_query($conn, "INSERT INTO " . TB_PREFIX . "users (id, username, email, gold) VALUES (8901, 'goldtester', 'gold@example.com', 10)");

        $code = 'GIFT' . random_int(1000, 9999);
        list($created, $msg) = \GoldShop::createCode($code, 25, 1, 1, 0, 'test', 1);
        $this->assertTrue($created, $msg);

        $list = \GoldShop::listCodes();
        $this->assertNotEmpty($list);

        list($ok, $rMsg, $awarded) = \GoldShop::redeem(8901, strtolower($code));
        $this->assertTrue($ok, $rMsg);
        $this->assertEquals(25, $awarded);

        list($dup, ) = \GoldShop::redeem(8901, $code);
        $this->assertFalse($dup);

        $res = mysqli_query($conn, "SELECT gold FROM " . TB_PREFIX . "users WHERE id = 8901");
        $row = mysqli_fetch_assoc($res);
        $this->assertEquals(35, (int)$row['gold']);
    }
}
