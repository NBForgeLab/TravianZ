<?php

namespace App\Admin\Services;

class PushProtectionService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function setOverride(int $uid, int $mode, int $customLimit, string $note, int $adminId): void
    {
        \PushProtection::setOverride($uid, $mode, $customLimit, $note, $adminId);

        $modeLabel = $mode === \PushProtection::OV_UNLIMITED ? 'exempt'
            : ($mode === \PushProtection::OV_CUSTOM ? ('custom cap ' . $customLimit) : 'cleared');
        $logMsg = 'Push protection override for uid ' . $uid . ' set to <b>' . $modeLabel . '</b>';
        $logEsc = $this->database->escape($logMsg);
        
        $this->database->query("INSERT INTO " . TB_PREFIX . "admin_log VALUES (0, " . $adminId . ", '" . $logEsc . "', " . time() . ")");
    }
}
