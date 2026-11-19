<?php

namespace App\Admin\Services;

class CpService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function updateCp(int $id, int $cp)
    {
        $this->database->query("UPDATE ".TB_PREFIX."users SET cp = cp + $cp WHERE id = $id");
    }
}
