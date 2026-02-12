<?php
declare(strict_types=1);

use App\Legacy\TemplateRenderer;
use PHPUnit\Framework\TestCase;

final class TemplateRendererTest extends TestCase
{
    public function testRenderStringReplacesTokens(): void
    {
        $result = TemplateRenderer::renderString('Hello {NAME}!', ['{NAME}' => 'Bob']);

        $this->assertSame('Hello Bob!', $result);
    }

    public function testRenderFileThrowsWhenMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template file not found');

        TemplateRenderer::renderFile(__DIR__ . '/__missing__.tpl', []);
    }
}

