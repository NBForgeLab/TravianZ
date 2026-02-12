<?php
declare(strict_types=1);



namespace App\Entity;

use App\Database\IDbConnection;

/**
 * Defines the properties of a user, e.g. player entity
 * connected to their profile and other personal and account-specific
 * information.
 *
 * @author martinambrus
 */
class User
{
    /**
     * @var int Database ID of the user.
     */
    private int $id;

    /**
     * @var string A unique username for this user.
     */
    private string $username;

    /**
     *
     * @var IDbConnection Database connection to perform queries on.
     */
    private IDbConnection $db;

    /**
     * Constructor for the User class.
     * Depending on the parameter input, a User class with
     * database ID or username will be instantiated.
     *
     * @example $user = new User(1);
     * @example $user = new User("martinambrus");
     *
     * @param int|string    $identifier ID or username for this user.
     * @param IDbConnection $database   Instance of the database class to use to perform queries.
     *
     * @return void This method doesn't have a return value.
     */
    public function __construct(int|string $identifier, IDbConnection $database)
    {
        // check if we passed an ID or a username
        if (is_int($identifier)) {
            $this->id = $identifier;
        } else {
            $this->username = $identifier;
        }

        $this->db = $database;
    }

    /**
     * Checks whether username or e-mail already exists in the database.
     *
     * @param  IDbConnection $db    The current database connection.
     * @param  string        $value Value to check names and emails for.
     * @return boolean       Returns true if the value exists in database,
     *                       false otherwise.
     */
    public static function exists(IDbConnection $db, string $value): bool
    {
        $sql = '(
                    SELECT
                        Count(*) AS Total
                    FROM
                        ' . TB_PREFIX . 'users
                    WHERE
                        username = ? OR email = ?
                )
                UNION ALL
                (
                    SELECT
                        Count(*) AS Total
                    FROM
                        ' . TB_PREFIX . 'activate
                    WHERE
                        username = ? OR email = ?
                )';

        $res = $db->query_new($sql, $value, $value, $value, $value);

        if (!is_array($res)) {
            return false;
        }
        $row0 = $res[0] ?? null;
        $row1 = $res[1] ?? null;
        $t0 = (is_array($row0) && array_key_exists('Total', $row0)) ? (int) $row0['Total'] : 0;
        $t1 = (is_array($row1) && array_key_exists('Total', $row1)) ? (int) $row1['Total'] : 0;
        return ($t0 > 0 || $t1 > 0);
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getUsername(): ?string
    {
        return $this->username ?? null;
    }

    public function getDb(): IDbConnection
    {
        return $this->db;
    }
}
