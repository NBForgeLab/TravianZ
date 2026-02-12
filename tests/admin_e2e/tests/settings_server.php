<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('حفظ إعدادات السيرفر يحدّث GameEngine/config.php', function (): void {
    $db = db();
    $user = ensureTestAdminUser($db);
    $snapshot = new ConfigSnapshot(ADMIN_CONFIG_PATH);

    try {
        $post = [
            'id' => (string) $user->id,
            'error' => 'error_reporting (0);',
            'servername' => 'TravianZ E2E Server',
            'tzone' => defined('TIMEZONE') ? TIMEZONE : 'UTC',
            'lang' => defined('LANG') ? LANG : 'en',
            'speed' => '3',
            'gpack' => 'false',
            'incspeed' => (string) (defined('INCREASE_SPEED') ? INCREASE_SPEED : 1),
            'evasionspeed' => (string) (defined('EVASION_SPEED') ? EVASION_SPEED : 1),
            'tradercap' => (string) (defined('TRADER_CAPACITY') ? TRADER_CAPACITY : 500),
            'crannycap' => (string) (defined('CRANNY_CAPACITY') ? CRANNY_CAPACITY : 100),
            'trappercap' => (string) (defined('TRAPPER_CAPACITY') ? TRAPPER_CAPACITY : 10),
            'village_expand' => (string) (defined('CP') ? CP : 0),
            'demolish' => (string) (defined('DEMOLISH_LEVEL_REQ') ? DEMOLISH_LEVEL_REQ : 10),
            'storage_multiplier' => (string) (defined('STORAGE_MULTIPLIER') ? STORAGE_MULTIPLIER : 1),
            'quest' => (defined('QUEST') && QUEST) ? 'true' : 'false',
            'qtype' => (string) (defined('QTYPE') ? QTYPE : 25),
            'beginner' => (string) (defined('PROTECTION') ? PROTECTION : 3600),
            'ww' => (defined('WW') && WW) ? 'True' : 'False',
            'show_natars' => (defined('SHOW_NATARS') && SHOW_NATARS) ? 'True' : 'False',
            'natars_units' => (string) (defined('NATARS_UNITS') ? NATARS_UNITS : 1),
            'natars_spawn_time' => (string) (defined('NATARS_SPAWN_TIME') ? NATARS_SPAWN_TIME : 86400),
            'natars_ww_spawn_time' => (string) (defined('NATARS_WW_SPAWN_TIME') ? NATARS_WW_SPAWN_TIME : 86400),
            'natars_ww_building_plan_spawn_time' => (string) (defined('NATARS_WW_BUILDING_PLAN_SPAWN_TIME') ? NATARS_WW_BUILDING_PLAN_SPAWN_TIME : 86400),
            'nature_regtime' => (string) (defined('NATURE_REGTIME') ? NATURE_REGTIME : 86400),
            'oasis_wood_multiplier' => (string) (defined('OASIS_WOOD_MULTIPLIER') ? OASIS_WOOD_MULTIPLIER : 1),
            'oasis_clay_multiplier' => (string) (defined('OASIS_CLAY_MULTIPLIER') ? OASIS_CLAY_MULTIPLIER : 1),
            'oasis_iron_multiplier' => (string) (defined('OASIS_IRON_MULTIPLIER') ? OASIS_IRON_MULTIPLIER : 1),
            'oasis_crop_multiplier' => (string) (defined('OASIS_CROP_MULTIPLIER') ? OASIS_CROP_MULTIPLIER : 1),
            'activate' => (defined('AUTH_EMAIL') && AUTH_EMAIL) ? 'true' : 'false',
            'medalinterval' => (string) (defined('MEDALINTERVAL') ? MEDALINTERVAL : 0),
            'great_wks' => (defined('GREAT_WKS') && GREAT_WKS) ? 'true' : 'false',
            'ts_threshold' => (string) (defined('TS_THRESHOLD') ? TS_THRESHOLD : 1),
            'reg_open' => (defined('REG_OPEN') && REG_OPEN) ? 'True' : 'False',
            'peace' => (string) (defined('PEACE') ? PEACE : 0),
        ];

        runAdminMod(ROOT_DIR . '/GameEngine/Admin/Mods/editServerSet.php', $post, ['access' => 9]);

        $cfg = file_get_contents(ADMIN_CONFIG_PATH);
        assertTrue(is_string($cfg) && $cfg !== '', 'تعذر قراءة config.php بعد الحفظ');

        assertContains('define("SERVER_NAME","TravianZ E2E Server");', $cfg);
        assertTrue((bool) preg_match('/define\\("SPEED",\\s*"3"\\);/', $cfg), 'لم يتم تحديث SPEED');

        $res = $db->query(
            "SELECT COUNT(*) AS c FROM `" . TB_PREFIX . "admin_log` WHERE `user`='" . $db->real_escape_string((string) $user->id) . "' AND `log`='Changed General Server Settings'"
        );
        $row = $res ? $res->fetch_assoc() : null;
        $count = (int) ($row['c'] ?? 0);
        assertTrue($count >= 1, 'لم يتم تسجيل حدث تغيير إعدادات السيرفر في admin_log');
    } finally {
        $snapshot->restore();
        cleanupTestAdminUser($db, $user);
    }
});

