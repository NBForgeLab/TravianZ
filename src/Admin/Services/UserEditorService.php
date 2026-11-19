<?php
namespace App\Admin\Services;

class UserEditorService {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function updateProfile($id, $data) {
        $email_raw = trim($data['email'] ?? '');
        $email     = filter_var($email_raw, FILTER_VALIDATE_EMAIL) ? $email_raw : '';
        $email     = $this->database->escape($email);

        $tribe    = max(1, min(9, (int)($data['tribe'] ?? 1)));
        
        $location_raw = trim($data['location'] ?? '');
        $location = $this->database->escape(mb_substr(strip_tags($location_raw), 0, 50));

        $desc1_raw = $data['desc1'] ?? '';
        $desc1 = $this->database->escape(mb_substr(strip_tags($desc1_raw, '<b><i><u><br>'), 0, 5000));

        $desc2_raw = $data['desc2'] ?? '';
        $desc2 = $this->database->escape(mb_substr(strip_tags($desc2_raw, '<b><i><u><br>'), 0, 5000));

        $quest_raw = trim($data['quest'] ?? '');
        $quest = $this->database->escape(mb_substr(strip_tags($quest_raw), 0, 200));

        $this->database->query(
            "UPDATE " . TB_PREFIX . "users SET 
                email = '$email',
                tribe = $tribe,
                location = '$location',
                desc1 = '$desc1',
                desc2 = '$desc2',
                quest = '$quest'
             WHERE id = " . (int)$id
        );
    }
}
