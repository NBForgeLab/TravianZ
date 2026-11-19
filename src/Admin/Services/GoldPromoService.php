<?php

namespace App\Admin\Services;

class GoldPromoService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function withLink(callable $fn)
    {
        $injected = ($this->db && isset($this->db->dblink) && $this->db->dblink instanceof \mysqli);
        if ($injected) {
            \GoldShop::setLink($this->db->dblink);
        }
        try {
            return $fn();
        } finally {
            if ($injected) {
                \GoldShop::setLink(null);
            }
        }
    }

    public function createCode($code, $gold, $maxUses, $perUser, $expires, $note, $adminId)
    {
        return $this->withLink(fn() => \GoldShop::createCode($code, $gold, $maxUses, $perUser, $expires, $note, $adminId));
    }

    public function setActive($id, $active)
    {
        return $this->withLink(fn() => \GoldShop::setActive($id, $active));
    }

    public function deleteCode($id)
    {
        return $this->withLink(fn() => \GoldShop::deleteCode($id));
    }
}
