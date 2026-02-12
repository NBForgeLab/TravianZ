<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('حفظ إعدادات NewsBox يحدّث GameEngine/config.php', function (): void {
    $db = db();
    $user = ensureTestAdminUser($db);
    $snapshot = new ConfigSnapshot(ADMIN_CONFIG_PATH);

    try {
        $post = [
            'id' => (string) $user->id,
            'box1' => 'false',
            'box2' => 'true',
            'box3' => 'false',
        ];

        runAdminMod(ROOT_DIR . '/GameEngine/Admin/Mods/editNewsboxSet.php', $post, ['access' => 9]);

        $cfg = file_get_contents(ADMIN_CONFIG_PATH);
        assertTrue(is_string($cfg) && $cfg !== '', 'تعذر قراءة config.php بعد الحفظ');

        assertTrue((bool) preg_match('/define\\(\"NEWSBOX1\",\\s*false\\);/', $cfg), 'لم يتم تحديث NEWSBOX1');
        assertTrue((bool) preg_match('/define\\(\"NEWSBOX2\",\\s*true\\);/', $cfg), 'لم يتم تحديث NEWSBOX2');
        assertTrue((bool) preg_match('/define\\(\"NEWSBOX3\",\\s*false\\);/', $cfg), 'لم يتم تحديث NEWSBOX3');

        $res = $db->query(
            "SELECT COUNT(*) AS c FROM `" . TB_PREFIX . "admin_log` WHERE `user`='" . $db->real_escape_string((string) $user->id) . "' AND `log`='Changed NewsBox Settings'"
        );
        $row = $res ? $res->fetch_assoc() : null;
        $count = (int) ($row['c'] ?? 0);
        assertTrue($count >= 1, 'لم يتم تسجيل حدث تغيير NewsBox في admin_log');
    } finally {
        $snapshot->restore();
        cleanupTestAdminUser($db, $user);
    }
});

