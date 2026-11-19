<?php
namespace App\Repository;

use App\Database\IDbConnection;

class UserRepository {
    private $db;

    public function __construct(IDbConnection $db) {
        $this->db = $db;
    }

    public function exists($value) {
        $sql = '(
                    SELECT
                        Count(*) AS Total
                    FROM
                        '.TB_PREFIX.'users
                    WHERE
                        username = ? OR email = ?
                )
                UNION ALL
                (
                    SELECT
                        Count(*) AS Total
                    FROM
                        '.TB_PREFIX.'activate
                    WHERE
                        username = ? OR email = ?
                )';

        $res = $this->db->query_new($sql, $value, $value, $value, $value);

        return ($res[0]['Total'] > 0 || (!empty($res[1]) && $res[1]['Total'] > 0));
    }

}
