<?php

namespace Tests\Admin;

require_once __DIR__ . '/../DatabaseTestCase.php';

use App\Admin\Services\MassMessageService;
use Tests\DatabaseTestCase;

class MassMessageServiceTest extends DatabaseTestCase
{
    public function testSendMassMessageInsertsFormattedMessageForPlayers()
    {
        $db = $GLOBALS['database'];
        $uid = 7777;

        $db->query("DELETE FROM " . TB_PREFIX . "mdata WHERE target = $uid");
        $db->query("DELETE FROM " . TB_PREFIX . "users WHERE id = $uid");
        $db->query("INSERT INTO " . TB_PREFIX . "users (id, username, email) VALUES ($uid, 'massmsguser', 'mass@example.com')");

        $service = new MassMessageService($db);
        $service->sendMassMessage(
            'Announcement',
            'Visit [url=https://example.com]site[/url] and ignore [url=javascript:alert(1)]bad[/url]',
            'red'
        );

        $res = mysqli_query($GLOBALS['link'], "SELECT topic, message FROM " . TB_PREFIX . "mdata WHERE target = $uid ORDER BY id DESC LIMIT 1");
        $row = mysqli_fetch_assoc($res);
        $this->assertNotNull($row);
        $this->assertStringContainsString('color:red;', $row['topic']);
        $this->assertStringContainsString('Announcement', $row['topic']);
        $this->assertStringContainsString('<a href="https://example.com"', $row['message']);
        $this->assertStringNotContainsString('javascript:', $row['message']);
    }
}
