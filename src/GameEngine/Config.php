<?php

namespace App\GameEngine;

class Config implements IConfig
{
    public function get(string $key)
    {
        return defined($key) ? constant($key) : null;
    }
}
