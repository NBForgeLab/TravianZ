<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('حفظ إعدادات السجلات يحدّث GameEngine/config.php', function (): void {
    $db = db();
    $user = ensureTestAdminUser($db);
    $snapshot = new ConfigSnapshot(ADMIN_CONFIG_PATH);

    try {
        $post = [
            'id' => (string) $user->id,
            'log_build' => 'true',
            'log_tech' => 'false',
            'log_login' => 'true',
            'log_gold_fin' => 'false',
            'log_admin' => 'true',
            'log_war' => 'false',
            'log_market' => 'true',
            'log_illegal' => 'false',
        ];

        runAdminMod(ROOT_DIR . '/GameEngine/Admin/Mods/editLogSet.php', $post, ['access' => 9]);

        $cfg = file_get_contents(ADMIN_CONFIG_PATH);
        assertTrue(is_string($cfg) && $cfg !== '', 'تعذر قراءة config.php بعد الحفظ');

        assertTrue((bool) preg_match('/define\\(\"LOG_BUILD\",\\s*true\\);/', $cfg), 'لم يتم تحديث LOG_BUILD');
        assertTrue((bool) preg_match('/define\\(\"LOG_TECH\",\\s*false\\);/', $cfg), 'لم يتم تحديث LOG_TECH');
        assertTrue((bool) preg_match('/define\\(\"LOG_LOGIN\",\\s*true\\);/', $cfg), 'لم يتم تحديث LOG_LOGIN');

        $res = $db->query(
            "SELECT COUNT(*) AS c FROM `" . TB_PREFIX . "admin_log` WHERE `user`='" . $db->real_escape_string((string) $user->id) . "' AND `log`='Changed Log Settings'"
        );
        $row = $res ? $res->fetch_assoc() : null;
        $count = (int) ($row['c'] ?? 0);
        assertTrue($count >= 1, 'لم يتم تسجيل حدث تغيير Log في admin_log');
    } finally {
        $snapshot->restore();
        cleanupTestAdminUser($db, $user);
    }
});

