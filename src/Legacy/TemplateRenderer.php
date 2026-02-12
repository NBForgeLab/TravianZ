<?php

declare(strict_types=1);

namespace App\Legacy;

final class TemplateRenderer
{
    /**
     * @param array<array-key, mixed> $replacements
     */
    public static function renderString(string $template, array $replacements): string
    {
        return strtr($template, self::normalizeReplacements($replacements));
    }

    /**
     * @param array<array-key, mixed> $replacements
     */
    public static function renderFile(string $filePath, array $replacements): string
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException('Template file not found: ' . $filePath);
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read template file: ' . $filePath);
        }

        return self::renderString($contents, $replacements);
    }

    /**
     * @param array<array-key, mixed> $replacements
     * @return array<string, string>
     */
    private static function normalizeReplacements(array $replacements): array
    {
        $normalized = [];
        foreach ($replacements as $key => $value) {
            if ($value !== null && !is_scalar($value) && !($value instanceof \Stringable)) {
                throw new \InvalidArgumentException('Replacement values must be scalar, null, or Stringable.');
            }
            $normalized[(string) $key] = (string) $value;
        }

        return $normalized;
    }
}
