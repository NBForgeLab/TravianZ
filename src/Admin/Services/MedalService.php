<?php

namespace App\Admin\Services;

class MedalService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function deleteMedal(int $medalId, int $adminId, int $userId, string $userName): void
    {
        $this->database->query("UPDATE " . TB_PREFIX . "medal SET del = 1 WHERE id = " . $medalId);
        $this->logAction($adminId, "Deleted medal id [#" . $medalId . "] from the user <a href='admin.php?p=player&uid=" . $userId . "'>" . $userName . "</a>");
    }

    public function deleteMedalsForUser(int $userId, int $adminId, string $userName): void
    {
        $this->database->query("UPDATE " . TB_PREFIX . "medal SET del = 1 WHERE userid = " . $userId);
        $this->logAction($adminId, "Deleted medals from the user <a href='admin.php?p=player&uid=" . $userId . "'>" . $userName . "</a>");
    }

    private function logAction(int $adminId, string $message): void
    {
        $message = $this->database->escape($message);
        $this->database->query("INSERT INTO " . TB_PREFIX . "admin_log VALUES (0, " . $adminId . ", '" . $message . "', " . time() . ")");
    }
}
