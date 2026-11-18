<?php
namespace App\GameEngine;

class ChatService {
    private $session;
    private $database;

    public function __construct($session, $database) {
        $this->session = $session;
        $this->database = $database;
    }

    public function addData($data) {
        if (is_array($data)){$msg = htmlspecialchars($data[1]);}else{$msg = htmlspecialchars($data);};
        $msg = $this->database->escape($msg);
        $name = addslashes($this->session->username);

        if ($msg != ""){
            $id_user = (int) $this->session->uid;
            $alliance = $this->database->escape($this->session->alliance);
            $now = time();
                $q = "INSERT into ".TB_PREFIX."chat (id_user,name,alli,date,msg) values ($id_user,'$name','$alliance','$now','$msg')";
                mysqli_query($this->database->dblink,$q);
        }
    }

    public function getData() {
        $data = '';

        $alliance = $this->database->escape($this->session->alliance);
        $query = mysqli_query($this->database->dblink,"select id_user, name, date, msg from ".TB_PREFIX."chat where alli='$alliance' order by id desc limit 0,13");
            while ($r = mysqli_fetch_array($query)) {
            $dates = date("g:i",$r['date']);
            $data .= "[{$dates}] <a href='spieler.php?uid={$r['id_user']}'>{$r['name']}</a>: {$r['msg']} <br>";
            }
        return $data;
    }
}
