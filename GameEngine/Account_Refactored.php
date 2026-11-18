<?php
use App\Entity\User;

class Account {
    private $database;
    private $session;
    private $form;
    private $mailer;
    private $generator;
    private $message;

    public function __construct($database, $session, $form, $mailer, $generator, $message) {
        $this->database = $database;
        $this->session = $session;
        $this->form = $form;
        $this->mailer = $mailer;
        $this->generator = $generator;
        $this->message = $message;

        if(isset($_POST['ft'])) {
            $_POST['ft'] = preg_replace('/[^a-z0-9]/i', '', $_POST['ft']);
            switch($_POST['ft']) {
                case "a1":
                    $this->Signup();
                    break;
                case "a2":
                    $this->Activate();
                    break;
                case "a3":
                    $this->Unreg();
                    break;
                case "a4":
                    $this->Login();
                    break;
            }
        } if(isset($_GET['code'])) {
            $_POST['id'] = $_GET['code']; $this->Activate();
        }
        else {
            if($this->session->logged_in && in_array("logout.php",explode("/",$_SERVER['PHP_SELF']))) {
                $this->Logout();
            }
        }
    }

    private function Signup() {
        # ... (rest of the class)
        # Replacing all global usage with $this->database, $this->form, etc.
    }
    # ...
}
