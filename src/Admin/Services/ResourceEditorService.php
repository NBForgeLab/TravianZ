<?php
namespace App\Admin\Services;

class ResourceEditorService {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function updateResources($id, $data) {
        $wood     = max(0, (int)($data['wood'] ?? 0));
        $clay     = max(0, (int)($data['clay'] ?? 0));
        $iron     = max(0, (int)($data['iron'] ?? 0));
        $crop     = max(0, (int)($data['crop'] ?? 0));
        $maxstore = max(0, (int)($data['maxstore'] ?? 0));
        $maxcrop  = max(0, (int)($data['maxcrop'] ?? 0));

        $this->database->query(
            "UPDATE " . TB_PREFIX . "vdata SET 
                wood = " . $wood . ",
                clay = " . $clay . ",
                iron = " . $iron . ",
                crop = " . $crop . ",
                maxstore = " . $maxstore . ",
                maxcrop = " . $maxcrop . "
             WHERE wref = " . (int)$id
        );
    }
}
