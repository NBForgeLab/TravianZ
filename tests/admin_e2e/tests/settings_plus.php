<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('حفظ إعدادات PLUS يحدّث GameEngine/config.php', function (): void {
    $db = db();
    $user = ensureTestAdminUser($db);
    $snapshot = new ConfigSnapshot(ADMIN_CONFIG_PATH);

    try {
        $post = [
            'id' => (string) $user->id,
            'plus_time' => '777',
            'plus_production' => '50',
            'paypal-email' => 'billing@travianz.test',
            'paypal-currency' => 'EUR',
            'plus-a-gold' => '60',
            'plus-a-price' => '1,99',
            'plus-b-gold' => '120',
            'plus-b-price' => '4,99',
            'plus-c-gold' => '360',
            'plus-c-price' => '9,99',
            'plus-d-gold' => '1000',
            'plus-d-price' => '19,99',
            'plus-e-gold' => '2000',
            'plus-e-price' => '49,99',
        ];

        runAdminMod(ROOT_DIR . '/GameEngine/Admin/Mods/editPlusSet.php', $post, ['access' => 9]);

        $cfg = file_get_contents(ADMIN_CONFIG_PATH);
        assertTrue(is_string($cfg) && $cfg !== '', 'تعذر قراءة config.php بعد الحفظ');

        assertTrue((bool) preg_match('/define\\(\"PLUS_TIME\",\\s*777\\);/', $cfg), 'لم يتم تحديث PLUS_TIME');
        assertTrue((bool) preg_match('/define\\(\"PLUS_PRODUCTION\",\\s*50\\);/', $cfg), 'لم يتم تحديث PLUS_PRODUCTION');
        assertContains('define("PAYPAL_EMAIL","billing@travianz.test");', $cfg);
        assertContains('define("PAYPAL_CURRENCY","EUR");', $cfg);

        $res = $db->query(
            "SELECT COUNT(*) AS c FROM `" . TB_PREFIX . "admin_log` WHERE `user`='" . $db->real_escape_string((string) $user->id) . "' AND `log`='Changed PLUS Settings'"
        );
        $row = $res ? $res->fetch_assoc() : null;
        $count = (int) ($row['c'] ?? 0);
        assertTrue($count >= 1, 'لم يتم تسجيل حدث تغيير PLUS في admin_log');
    } finally {
        $snapshot->restore();
        cleanupTestAdminUser($db, $user);
    }
});

