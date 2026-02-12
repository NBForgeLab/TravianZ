<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('تسجيل دخول الأدمن عبر adm_DB::Login()', function (): void {
    $db = db();
    $user = ensureTestAdminUser($db);

    try {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        require_once ROOT_DIR . '/GameEngine/Admin/database.php';

        $admDb = new \adm_DB();
        $ok = $admDb->Login($user->username, $user->password);
        assertTrue($ok === true, 'فشل تسجيل الدخول');

        $res = $db->query(
            "SELECT COUNT(*) AS c FROM `" . TB_PREFIX . "admin_log` WHERE `log` LIKE '%" . $db->real_escape_string($user->username) . " logged in%'"
        );
        $row = $res ? $res->fetch_assoc() : null;
        $count = (int) ($row['c'] ?? 0);
        assertTrue($count >= 1, 'لم يتم تسجيل حدث الدخول في admin_log');
    } finally {
        cleanupTestAdminUser($db, $user);
    }
});

