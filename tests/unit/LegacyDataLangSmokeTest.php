<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class LegacyDataLangSmokeTest extends TestCase
{
    private function gameEnginePath(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        $root = dirname(__DIR__, 2);
        $candidates = [
            $root . '/GameEngine/' . $relativePath,
            $root . '/gameengine/' . $relativePath,
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $this->fail('Missing required file: ' . $relativePath);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDataResdataLoadsAndDefinesExpectedArrays(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Data/resdata.php');

        $this->assertTrue(isset($r2));
        $this->assertIsArray($r2);
        $this->assertArrayHasKey('wood', $r2);
        $this->assertArrayHasKey('clay', $r2);
        $this->assertArrayHasKey('iron', $r2);
        $this->assertArrayHasKey('crop', $r2);
        $this->assertArrayHasKey('time', $r2);

        $this->assertTrue(isset($ab1));
        $this->assertIsArray($ab1);
        $this->assertArrayHasKey(1, $ab1);
        $this->assertIsArray($ab1[1]);
        $this->assertArrayHasKey('wood', $ab1[1]);
        $this->assertArrayHasKey('time', $ab1[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDataUnitdataLoadsAndDefinesExpectedArrays(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Data/unitdata.php');

        $this->assertTrue(isset($unitsbytype));
        $this->assertIsArray($unitsbytype);
        $this->assertArrayHasKey('infantry', $unitsbytype);
        $this->assertIsArray($unitsbytype['infantry']);

        $this->assertTrue(isset($u1));
        $this->assertIsArray($u1);
        $this->assertArrayHasKey('atk', $u1);
        $this->assertArrayHasKey('di', $u1);
        $this->assertArrayHasKey('dc', $u1);
        $this->assertArrayHasKey('wood', $u1);
        $this->assertArrayHasKey('time', $u1);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDataBuidataLoadsAndDefinesExpectedArrays(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Data/buidata.php');

        $this->assertTrue(isset($bid1));
        $this->assertIsArray($bid1);
        $this->assertArrayHasKey(1, $bid1);
        $this->assertIsArray($bid1[1]);
        $this->assertArrayHasKey('wood', $bid1[1]);
        $this->assertArrayHasKey('time', $bid1[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDataCpLoadsAndDefinesExpectedArray(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Data/cp.php');

        $this->assertTrue(isset($cp0));
        $this->assertIsArray($cp0);
        $this->assertArrayHasKey(2, $cp0);
        $this->assertIsInt($cp0[2]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDataCelLoadsAndDefinesExpectedArrays(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Data/cel.php');

        $this->assertTrue(isset($cel));
        $this->assertIsArray($cel);
        $this->assertArrayHasKey(1, $cel);
        $this->assertIsArray($cel[1]);
        $this->assertArrayHasKey('name', $cel[1]);
        $this->assertArrayHasKey('time', $cel[1]);

        $this->assertTrue(isset($sc));
        $this->assertIsArray($sc);
        $this->assertArrayHasKey(1, $sc);
        $this->assertIsInt($sc[1]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDataHeroFullLoadsAndDefinesExpectedArrays(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Data/hero_full.php');

        $this->assertTrue(isset($hero_levels));
        $this->assertIsArray($hero_levels);
        $this->assertArrayHasKey(0, $hero_levels);
        $this->assertIsInt($hero_levels[0]);

        $this->assertTrue(isset($h1_full));
        $this->assertIsArray($h1_full);
        $this->assertArrayHasKey(0, $h1_full);
        $this->assertIsArray($h1_full[0]);
        $this->assertArrayHasKey('wood', $h1_full[0]);
        $this->assertArrayHasKey('time', $h1_full[0]);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLangEnLoadsAndDefinesExpectedConstants(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Lang/en.php');

        $this->assertTrue(defined('TRAVIANZ_LANG_EN_LOADED'));
        $this->assertTrue(defined('TRIBE1'));
        $this->assertTrue(defined('HOME'));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLangItLoadsAndDefinesExpectedConstants(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Lang/it.php');

        $this->assertTrue(defined('TRAVIANZ_LANG_IT_LOADED'));
        $this->assertTrue(defined('TRIBE1'));
        $this->assertTrue(defined('HOME'));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testLangZhTwLoadsAndDefinesExpectedConstants(): void
    {
        require $this->gameEnginePath('config.php');
        require $this->gameEnginePath('Lang/zh_tw.php');

        $this->assertTrue(defined('TRAVIANZ_LANG_ZH_TW_LOADED'));
        $this->assertTrue(defined('TRIBE1'));
        $this->assertTrue(defined('HOME'));
    }
}
