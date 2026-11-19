<?php

namespace App\Admin\Services;

class DebugLogService
{
    private $database;
    private $logFile;

    public function __construct($database, string $logFile)
    {
        $this->database = $database;
        $this->logFile = $logFile;
    }

    public function saveSettings($lvl_warning, $lvl_notice, $lvl_deprecated, $lvl_fatal, $max_size, $auto_off, $uid)
    {
        $this->database->setDebugSettings(
            (bool)$lvl_warning,
            (bool)$lvl_notice,
            (bool)$lvl_deprecated,
            (bool)$lvl_fatal,
            (int)$max_size,
            (int)$auto_off
        );
        $this->database->query("Insert into ".TB_PREFIX."admin_log values (0,".$uid.",'Changed Debug Error Log settings',".time().")");
    }

    public function toggle($active, $uid)
    {
        $active = (int)$active;
        $this->database->setDebugMode($active, $uid);
        $this->database->query("Insert into ".TB_PREFIX."admin_log values (0,".$uid.",'".($active ? 'Enabled' : 'Disabled')." Debug Error Log',".time().")");
    }

    public function clear($uid)
    {
        @file_put_contents($this->logFile, '');
        @unlink($this->logFile . '.1');
        $this->database->query("Insert into ".TB_PREFIX."admin_log values (0,".$uid.",'Cleared Debug Error Log',".time().")");
    }
}
