<?php
include '../model/UzivatelModel.php';
include '../conf/connect.php';

class UzivatelController {
    private $uzivatelModel;

    public function __construct($dbConnection) {
        $this->uzivatelModel = new UzivatelModel($dbConnection);
    }

    public function registrace() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if ($this->uzivatelModel->registrace($username, $password)) {
                // Registrace byla úspěšná
                header('Location: ../view/prihlaseni.php'); // Přesměrování na přihlašovací stránku
            } else {
                // Registrace selhala (uživatelské jméno již existuje)
                echo "Uživatelské jméno již existuje.";
            }
        }
    }

    public function prihlaseni() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $uzivatel = $this->uzivatelModel->overeniUzivatele($username, $password);

            if ($uzivatel) {
                session_start();
                $_SESSION['uzivatel_id'] = $uzivatel['id'];
                $_SESSION['uzivatel_jmeno'] = $uzivatel['username'];
                // Přesměrování na hlavní stránku
                header('Location: ../view/KvizVyber.php');
                exit;

            } else {
                // Přihlášení selhalo
                echo "Nesprávné uživatelské jméno nebo heslo.";
            }
        }
    }
}

$controller = new UzivatelController($conn);
if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $controller->registrace();
} elseif (isset($_POST['action']) && $_POST['action'] == 'login') {
    $controller->prihlaseni();
}
?>