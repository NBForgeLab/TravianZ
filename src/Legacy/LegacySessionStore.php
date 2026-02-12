<?php

declare(strict_types=1);

namespace App\Legacy;

final class LegacySessionStore implements FormStore
{
    public function getErrorArray(): array
    {
        $errors = $_SESSION['errorarray'] ?? [];
        if (!is_array($errors)) {
            return [];
        }
        $out = [];
        foreach ($errors as $k => $v) {
            if (is_string($k) && is_scalar($v)) {
                $out[$k] = (string) $v;
            }
        }
        return $out;
    }

    public function getValueArray(): array
    {
        $values = $_SESSION['valuearray'] ?? [];
        if (!is_array($values)) {
            return [];
        }
        $out = [];
        foreach ($values as $k => $v) {
            if (is_string($k) && is_scalar($v)) {
                $out[$k] = (string) $v;
            }
        }
        return $out;
    }

    public function clear(): void
    {
        unset($_SESSION['errorarray'], $_SESSION['valuearray']);
    }
}
