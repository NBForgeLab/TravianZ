<?php

declare(strict_types=1);

namespace App\Legacy;

interface FormStore
{
    /**
     * @return array<string, string>
     */
    public function getErrorArray(): array;
    /**
     * @return array<string, string>
     */
    public function getValueArray(): array;
    public function clear(): void;
}
