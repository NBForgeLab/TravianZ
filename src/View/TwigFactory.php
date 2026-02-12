<?php

declare(strict_types=1);

namespace App\View;

final class TwigFactory
{
    private static ?\Twig\Environment $env = null;

    public static function get(string $projectRoot): \Twig\Environment
    {
        if (self::$env instanceof \Twig\Environment) {
            return self::$env;
        }

        $templatesDirs = [
            $projectRoot . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'modern',
            $projectRoot . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . 'modern',
        ];

        $loader = new \Twig\Loader\FilesystemLoader($templatesDirs);

        $cacheDir = $projectRoot . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'twig';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        self::$env = new \Twig\Environment($loader, [
            'cache' => is_dir($cacheDir) ? $cacheDir : false,
            'autoescape' => 'html',
        ]);

        return self::$env;
    }
}
