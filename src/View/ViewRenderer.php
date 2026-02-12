<?php

declare(strict_types=1);

namespace App\View;

final class ViewRenderer
{
    public function __construct(
        private readonly string $projectRoot,
    ) {
    }

    public static function fromProjectRoot(?string $projectRoot = null): self
    {
        $root = $projectRoot ?? \dirname(__DIR__, 2);

        return new self($root);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function renderPhp(string $templatePath, array $context = []): string
    {
        $absolutePath = $this->resolvePath($templatePath);

        if (!\file_exists($absolutePath)) {
            throw new \RuntimeException("View not found: {$templatePath}");
        }

        \ob_start();
        try {
            $this->includeTemplate($absolutePath, $context);
        } finally {
            $output = \ob_get_clean();
        }

        return $output === false ? '' : $output;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function displayPhp(string $templatePath, array $context = []): void
    {
        echo $this->renderPhp($templatePath, $context);
    }

    private function resolvePath(string $templatePath): string
    {
        if ($templatePath === '') {
            return $this->projectRoot;
        }

        if (\str_starts_with($templatePath, '/') || \preg_match('/^[A-Za-z]:\\\\/', $templatePath) === 1) {
            return $templatePath;
        }

        return $this->projectRoot . DIRECTORY_SEPARATOR . \str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templatePath);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function includeTemplate(string $absolutePath, array $context): void
    {
        if ($context !== []) {
            \extract($context, \EXTR_SKIP);
        }

        include $absolutePath;
    }
}
