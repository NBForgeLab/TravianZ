<?php

declare(strict_types=1);

namespace App\Legacy;

final class SessionHelper
{
    public static function putString(string $key, string $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function putInt(string $key, int $value): void
    {
        $_SESSION[$key] = $value;
    }
}
