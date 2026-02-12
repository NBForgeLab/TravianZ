<?php
declare(strict_types=1);

function normalizePath(string $path): string
{
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    return rtrim($path, DIRECTORY_SEPARATOR);
}

function isIgnoredPath(string $path): bool
{
    $path = str_replace('\\', '/', $path);
    return str_contains($path, '/vendor/')
        || str_contains($path, '/mariadb-data/')
        || str_contains($path, '/.git/')
        || str_contains($path, '/.idea/')
        || str_contains($path, '/.vscode/');
}

function collectPhpFiles(array $roots): array
{
    $files = [];

    foreach ($roots as $root) {
        $root = normalizePath($root);
        if (!file_exists($root)) {
            continue;
        }

        if (is_file($root)) {
            if (str_ends_with($root, '.php') && !isIgnoredPath($root)) {
                $files[] = $root;
            }
            continue;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $fileInfo) {
            if (!$fileInfo instanceof SplFileInfo) {
                continue;
            }
            if (!$fileInfo->isFile()) {
                continue;
            }

            $path = $fileInfo->getPathname();
            if (isIgnoredPath($path)) {
                continue;
            }
            if (!str_ends_with($path, '.php')) {
                continue;
            }

            $files[] = $path;
        }
    }

    $files = array_values(array_unique($files));
    sort($files);
    return $files;
}

function runPhpLint(array $files): int
{
    $php = PHP_BINARY;
    $failed = 0;

    foreach ($files as $file) {
        $cmd = escapeshellarg($php) . ' -l ' . escapeshellarg($file);
        $output = [];
        $exitCode = 0;
        @exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $failed++;
            fwrite(STDERR, implode(PHP_EOL, $output) . PHP_EOL);
        }
    }

    if ($failed > 0) {
        fwrite(STDERR, "PHP lint failed: {$failed} file(s)" . PHP_EOL);
        return 1;
    }

    fwrite(STDOUT, 'PHP lint OK (' . count($files) . ' files)' . PHP_EOL);
    return 0;
}

$rootDir = dirname(__DIR__);
chdir($rootDir);

$args = array_slice($argv, 1);
$roots = $args !== [] ? $args : [
    'src',
    'GameEngine',
    'Admin',
];

$roots[] = 'index.php';
$roots[] = 'router.php';

$files = collectPhpFiles($roots);
if ($files === []) {
    fwrite(STDERR, "No PHP files found to lint." . PHP_EOL);
    exit(1);
}

exit(runPhpLint($files));
