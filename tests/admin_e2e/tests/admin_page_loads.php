<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

runner()->add('Admin/admin.php يعرض صفحة الدخول', function (): void {
    $resp = httpGet('http://127.0.0.1:8080/Admin/admin.php');
    assertTrue($resp['status'] === 200, 'توقعنا HTTP 200');
    assertContains('Admin Panel', $resp['body'], 'العنوان غير موجود');
});

