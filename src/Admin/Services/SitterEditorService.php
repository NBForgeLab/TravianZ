<?php
namespace App\Admin\Services;

class SitterEditorService {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function updateSitters($id, $sit1, $sit2) {
        $sit1 = (int)$sit1;
        $sit2 = (int)$sit2;
        $this->database->query("UPDATE " . TB_PREFIX . "users SET sit1 = $sit1, sit2 = $sit2 WHERE id = " . (int)$id);
    }
}
