<?php

namespace App\GameEngine;

interface IConfig
{
    public function get(string $key);
}
