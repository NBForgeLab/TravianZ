<?php

namespace App\GameEngine;

class Config
{
    public function get(string $key)
    {
        return defined($key) ? constant($key) : null;
    }
}
