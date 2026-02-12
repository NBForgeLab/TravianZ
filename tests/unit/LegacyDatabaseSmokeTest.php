<?php
declare(strict_types=1);

use App\Database\IDbConnection;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class LegacyDatabaseSmokeTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDatabaseClassLoadsWithoutConnecting(): void
    {
        $old = getenv('TRAVIANZ_SKIP_DB_CONNECT');
        putenv('TRAVIANZ_SKIP_DB_CONNECT=1');

        require __DIR__ . '/../../GameEngine/config.php';
        require __DIR__ . '/../../GameEngine/Database.php';

        $this->assertTrue(class_exists(MYSQLi_DB::class));
        $this->assertTrue(is_subclass_of(MYSQLi_DB::class, IDbConnection::class));

        if ($old === false) {
            putenv('TRAVIANZ_SKIP_DB_CONNECT');
        } else {
            putenv('TRAVIANZ_SKIP_DB_CONNECT=' . $old);
        }
    }
}
