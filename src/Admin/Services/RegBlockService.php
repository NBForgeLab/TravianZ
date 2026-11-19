<?php

namespace App\Admin\Services;

class RegBlockService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    private function getLink()
    {
        if (is_object($this->database) && method_exists($this->database, 'return_link')) {
            return $this->database->return_link();
        }
        return $this->database->dblink ?? null;
    }

    public function ensureSchema()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . TB_PREFIX . "reg_block` (
            `id`       int(11) NOT NULL AUTO_INCREMENT,
            `type`     varchar(16) NOT NULL DEFAULT 'username',
            `value`    varchar(255) NOT NULL DEFAULT '',
            `note`     varchar(255) NOT NULL DEFAULT '',
            `added_by` int(11) NOT NULL DEFAULT 0,
            `time`     int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `type_value` (`type`,`value`),
            KEY `type` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $link = $this->getLink();
        if ($link instanceof \mysqli) {
            @mysqli_query($link, $sql);
        } elseif (is_object($this->database) && method_exists($this->database, 'query')) {
            $this->database->query($sql);
        }
    }

    private function norm($v)
    {
        return strtolower(trim((string) $v));
    }

    private function validType($t)
    {
        return in_array($t, ['username', 'email', 'domain'], true);
    }

    public function add($type, $value, $note, $adminId)
    {
        $link = $this->database->return_link();
        $this->ensureSchema();

        $type = (string) $type;
        if (!$this->validType($type)) {
            return [false, 'Invalid block type.'];
        }

        $value = $this->norm($value);
        if ($type === 'domain') {
            $value = ltrim($value, '@');
        }
        if ($value === '') {
            return [false, 'Value cannot be empty.'];
        }

        if ($type === 'email' && strpos($value, '@') === false) {
            return [false, 'That does not look like a full e-mail address.'];
        }
        if ($type === 'domain' && strpos($value, '.') === false) {
            return [false, 'That does not look like a domain (e.g. yahoo.com).'];
        }
        if (strlen($value) > 255) {
            return [false, 'Value too long.'];
        }

        $note  = substr((string) $note, 0, 255);
        $admin = (int) $adminId;
        $now   = time();

        $stmt = mysqli_prepare($link,
            "INSERT INTO `" . TB_PREFIX . "reg_block` (`type`,`value`,`note`,`added_by`,`time`)
             VALUES (?,?,?,?,?)
             ON DUPLICATE KEY UPDATE `note` = VALUES(`note`), `added_by` = VALUES(`added_by`), `time` = VALUES(`time`)");
        if (!$stmt) {
            return [false, 'Could not prepare statement.'];
        }
        mysqli_stmt_bind_param($stmt, 'sssii', $type, $value, $note, $admin, $now);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ok ? [true, 'Block saved.'] : [false, 'Could not save block.'];
    }

    public function remove($id)
    {
        $link = $this->database->return_link();
        $this->ensureSchema();
        $stmt = mysqli_prepare($link,
            "DELETE FROM `" . TB_PREFIX . "reg_block` WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $id = (int) $id;
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}
