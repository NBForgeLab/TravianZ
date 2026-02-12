<?php
declare(strict_types=1);

use App\Database\IDbConnection;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class DummyDb implements IDbConnection
{
    public function connect(): bool { return true; }
    public function disconnect(): bool { return true; }
    public function reconnect(): bool { return true; }
    public function is_connected(): bool { return true; }
    public function query_new(string $statement, mixed ...$params): mixed { return []; }
}

final class UserGettersTest extends TestCase
{
    public function testGettersWithId(): void
    {
        $db = new DummyDb();
        $user = new User(7, $db);
        $this->assertSame(7, $user->getId());
        $this->assertNull($user->getUsername());
        $this->assertSame($db, $user->getDb());
    }

    public function testGettersWithUsername(): void
    {
        $db = new DummyDb();
        $user = new User('alice', $db);
        $this->assertSame('alice', $user->getUsername());
        $this->assertNull($user->getId());
        $this->assertSame($db, $user->getDb());
    }
}
