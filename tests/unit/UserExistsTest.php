<?php
declare(strict_types=1);

use App\Database\IDbConnection;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class FakeDb implements IDbConnection
{
    public array $last = [];

    public function connect(): bool
    {
        return true;
    }
    public function disconnect(): bool
    {
        return true;
    }
    public function reconnect(): bool
    {
        return true;
    }
    public function is_connected(): bool
    {
        return true;
    }
    public function query_new(string $statement, mixed ...$params): mixed
    {
        $this->last = ['statement' => $statement, 'params' => $params];
        if (is_string($statement) && str_contains($statement, 'SELECT') && count($params) === 4) {
            return [['Total' => 1], ['Total' => 0]];
        }
        return [['Total' => 0], ['Total' => 0]];
    }
}

final class UserExistsTest extends TestCase
{
    public function testExistsReturnsTrueWhenTotalGreaterThanZero(): void
    {
        $db = new FakeDb();
        $this->assertTrue(User::exists($db, 'john@doe.com'));
        $this->assertNotEmpty($db->last);
        $this->assertCount(4, $db->last['params']);
    }

    public function testExistsReturnsFalseWhenTotalsZero(): void
    {
        $db = new class extends FakeDb {
            public function query_new(string $statement, mixed ...$params): mixed
            {
                $this->last = ['statement' => $statement, 'params' => $params];
                return [['Total' => 0], ['Total' => 0]];
            }
        };
        $this->assertFalse(User::exists($db, 'someone@example.com'));
    }
}
