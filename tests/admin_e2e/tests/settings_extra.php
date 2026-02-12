<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('حفظ إعدادات إضافية يحدّث GameEngine/config.php', function (): void {
    $db = db();
    $user = ensureTestAdminUser($db);
    $snapshot = new ConfigSnapshot(ADMIN_CONFIG_PATH);

    try {
        $post = [
            'id' => (string) $user->id,
            'limit_mailbox' => 'true',
        ];

        runAdminMod(ROOT_DIR . '/GameEngine/Admin/Mods/editExtraSet.php', $post, ['access' => 9]);

        $cfg = file_get_contents(ADMIN_CONFIG_PATH);
        assertTrue(is_string($cfg) && $cfg !== '', 'تعذر قراءة config.php بعد الحفظ');

        assertTrue((bool) preg_match('/define\\(\"LIMIT_MAILBOX\",\\s*true\\);/', $cfg), 'لم يتم تحديث LIMIT_MAILBOX');

        $res = $db->query(
            "SELECT COUNT(*) AS c FROM `" . TB_PREFIX . "admin_log` WHERE `user`='" . $db->real_escape_string((string) $user->id) . "' AND `log`='Changed Extra server settings'"
        );
        $row = $res ? $res->fetch_assoc() : null;
        $count = (int) ($row['c'] ?? 0);
        assertTrue($count >= 1, 'لم يتم تسجيل حدث تغيير Extra في admin_log');
    } finally {
        $snapshot->restore();
        cleanupTestAdminUser($db, $user);
    }
});

