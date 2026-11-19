<?php

namespace App\Admin\Services;

class MessageService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function sendMessage(int $targetUid, int $ownerUid, string $topic, string $message): void
    {
        $topicEsc = $this->database->escape($topic);
        $msgEsc   = $this->database->escape($message);
        $time = time();

        $sql = "
            INSERT INTO " . TB_PREFIX . "mdata 
            (target, owner, topic, message, viewed, archived, send, time, deltarget, delowner, alliance, player, coor, report)
            VALUES ($targetUid, $ownerUid, '$topicEsc', '$msgEsc', 0, 0, 0, $time, 0, 0, 0, 0, 0, 0)
        ";
        
        $this->database->query($sql);
        
        $logText = "Sent message to uid $targetUid: '$topic'";
        $logEsc  = $this->database->escape($logText);
        $this->database->query("
            INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`)
            VALUES (0, $ownerUid, '$logEsc', $time)
        ");
    }
}
