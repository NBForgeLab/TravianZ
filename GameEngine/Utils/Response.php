<?php
namespace App\Utils;

class Response {
    public static function redirect($url) {
        if (!defined('TRAVIANZ_TEST_MODE')) {
            header('Location: ' . $url);
            exit;
        }
        // In test mode, do not exit or redirect.
    }
}
