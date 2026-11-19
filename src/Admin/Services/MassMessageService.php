<?php

namespace App\Admin\Services;

use App\Database\IDbConnection;

class MassMessageService
{
    public function __construct(private IDbConnection $database)
    {
    }

    public function sendMassMessage(string $subject, string $message, string $color): void
    {
        $safeSubject = $this->database->escape($subject);
        $safeColor = $this->database->escape($color);
        
        $message = $this->formatBBCode($message);
        $safeMessage = $this->database->escape("[message]".$message."[/message]");

        $result = $this->database->query(
            "SELECT id
             FROM ".TB_PREFIX."users
             WHERE id > 5
             ORDER BY id ASC"
        );

        $rows = [];
        $time = time();

        if ($result instanceof \mysqli_result) {
            while ($user = mysqli_fetch_assoc($result)) {
                $uid = (int)$user['id'];
                $rows[] = "($uid, 1, '<span style=\"color:$safeColor;\">$safeSubject</span>', '$safeMessage', 0, 0, 0, $time, 0, 0, 0, 0, 0, 0)";
            }
        } elseif (is_iterable($result)) {
            foreach ($result as $user) {
                $uid = (int)$user['id'];
                $rows[] = "($uid, 1, '<span style=\"color:$safeColor;\">$safeSubject</span>', '$safeMessage', 0, 0, 0, $time, 0, 0, 0, 0, 0, 0)";
            }
        }

        if (!empty($rows)) {
            $this->database->query(
                "INSERT INTO ".TB_PREFIX."mdata
                (target, owner, topic, message, viewed, archived, send, time, deltarget, delowner, alliance, player, coor, report)
                VALUES " . implode(",", $rows)
            );
        }
    }

    private function formatBBCode(string $message): string
    {
        // Simple implementation of the BBCode logic from the original file
        $message = preg_replace_callback("/\[img\](.*?)\[\/img\]/is", function ($m) {
            $url = trim($m[1]);
            if (!preg_match('#^https?://#i', $url)) return '';
            return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" alt="" />';
        }, $message);

        $message = preg_replace_callback("/\[url\](.*?)\[\/url\]/is", function ($m) {
            $url = trim($m[1]);
            $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            if (!preg_match('#^https?://#i', $url)) return $safe;
            return '<a href="' . $safe . '" target="_blank" rel="noopener noreferrer">' . $safe . '</a>';
        }, $message);

        $message = preg_replace_callback("/\[url=(.*?)\](.*?)\[\/url\]/is", function ($m) {
            $url = trim($m[1]);
            $text = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            if (!preg_match('#^https?://#i', $url)) return $text;
            $safe = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            return '<a href="' . $safe . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
        }, $message);

        return $message;
    }
}
