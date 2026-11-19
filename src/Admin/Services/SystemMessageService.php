<?php

namespace App\Admin\Services;

class SystemMessageService
{
    private $database;
    private $autoprefix;

    public function __construct($database, $autoprefix)
    {
        $this->database = $database;
        $this->autoprefix = $autoprefix;
    }

    public function postSystemMessage(string $subject, string $message, string $color): void
    {
        $body  = '<div style="color:' . $color . ';font-weight:bold;font-size:14px;margin-bottom:8px">' . $subject . '</div>';
        $body .= $message;
        $body  = str_replace("\0", '', $body);

        $safe = str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $body);

        $format = @file_get_contents($this->autoprefix . 'Templates/text_format.tpl');
        if ($format === false) {
            throw new \Exception("Cannot read Templates/text_format.tpl");
        }

        $pattern = '/\$txt\s*=\s*"%TEKST%"\s*;/';
        $out     = preg_replace_callback(
            $pattern,
            static function () use ($safe): string {
                return '$txt = "' . $safe . '";';
            },
            $format,
            1,
            $count
        );

        if ($out === null || $count !== 1) {
            throw new \Exception("Cannot update Templates/text.tpl: the placeholder was not found exactly once in text_format.tpl");
        }

        if (@file_put_contents($this->autoprefix . 'Templates/text.tpl', $out) === false) {
            throw new \Exception("Cannot write Templates/text.tpl (check permissions)");
        }

        $this->database->setUsersOk(1);
    }
}
