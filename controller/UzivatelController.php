<?php
include '../model/UzivatelModel.php';
include '../conf/connect.php';
session_start();

class UzivatelController {
    private $uzivatelModel;

    public function __construct($dbConnection) {
        $this->uzivatelModel = new UzivatelModel($dbConnection);
    }

    public function registrace() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $password_confirm = $_POST['password_confirm'];

            // Ověření shody hesel
            if ($_POST['password'] !== $password_confirm) {
                $_SESSION['status_message'] = 'Registrace selhala. Zadaná hesla se neshodují.';
                $_SESSION['status_type'] = 'error';
                header('Location: ../view/registrace.php');
                exit;
            }
            
            // Kontrola honeypot pole
            if (!empty($_POST['confirm_password_honeypot'])) {
                // Pravděpodobně bot, ukončete skript nebo zalogujte pokus o registraci
                die("Bot detekován!");
            }

            if ($this->uzivatelModel->registrace($username, $password)) {
                // Registrace byla úspěšná
                $_SESSION['status_message'] = 'Registrace byla úspěšná. Nyní se můžete přihlásit.';
                $_SESSION['status_type'] = 'success'; // Typ může být 'success' nebo 'error'
                header('Location: ../view/prihlaseni.php');
                exit;
            } else {
                $_SESSION['status_message'] = 'Registrace selhala. Uživatelské jméno již existuje.';
                $_SESSION['status_type'] = 'error';
                header('Location: ../view/registrace.php');
                exit;
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
                $_SESSION['status_message'] = 'Přihlášení selhalo. Nesprávné jméno nebo heslo.';
                $_SESSION['status_type'] = 'error'; // Typ může být 'success' nebo 'error'
                header('Location: ../view/prihlaseni.php');
                exit;
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