<?php

namespace App\GameEngine;

class Cache
{
    private array $data = [];

    /**
     * Get an item from the cache.
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Set an item in the cache.
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if an item exists in the cache.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Clear all cache data.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Clear a specific key from the cache.
     */
    public function forget(string $key): void
    {
        unset($this->data[$key]);
    }
}
