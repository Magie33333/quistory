<?php
class UzivatelModel {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function registrace($username, $heslo) {
        // Zkontrolujte, zda uživatelské jméno již neexistuje
        $stmt = $this->db->prepare("SELECT * FROM uzivatele WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            // Uživatelské jméno již existuje
            return false;
        }

        // Vložení nového uživatele do databáze
        $stmt = $this->db->prepare("INSERT INTO uzivatele (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $heslo]);
        return true;
    }

    public function overeniUzivatele($username, $heslo) {
        // Ověření uživatele
        $stmt = $this->db->prepare("SELECT * FROM uzivatele WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch();
            if (password_verify($heslo, $user['password'])) {
                // Heslo je správné
                return $user;
            }
        }
        return false;
    }
}
?>