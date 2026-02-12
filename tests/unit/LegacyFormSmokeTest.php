<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class LegacyFormSmokeTest extends TestCase
{
    private function gameEnginePath(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        $root = dirname(__DIR__, 2);
        $candidates = [
            $root . '/GameEngine/' . $relativePath,
            $root . '/gameengine/' . $relativePath,
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $this->fail('Missing required file: ' . $relativePath);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testConstructorConsumesSessionArrays(): void
    {
        require $this->gameEnginePath('Form.php');

        $_SESSION = [
            'errorarray' => ['foo' => 'bad'],
            'valuearray' => ['foo' => 'bar'],
        ];

        $form = new Form();

        $this->assertSame(1, $form->returnErrors());
        $this->assertSame('bad', $form->getError('foo'));
        $this->assertSame('bar', $form->getValue('foo'));

        $this->assertFalse(isset($_SESSION['errorarray']));
        $this->assertFalse(isset($_SESSION['valuearray']));
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddErrorIncrementsErrorCount(): void
    {
        require $this->gameEnginePath('Form.php');

        $_SESSION = [];

        $form = new Form();
        $this->assertSame(0, $form->returnErrors());

        $form->addError('email', 'invalid');

        $this->assertSame(1, $form->returnErrors());
        $this->assertSame('invalid', $form->getError('email'));
    }
}

