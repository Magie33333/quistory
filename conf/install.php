<?php
include 'connect.php'; // Připojení k databázi

if (!$conn) {
    die("Nelze se připojit k databázi: " . mysqli_connect_error());
}

// Funkce pro vytvoření tabulky
function createTable($conn, $sql, $tableName) {
    try {
        $conn->exec($sql);
        echo "Tabulka '$tableName' byla úspěšně vytvořena.";
    } catch (PDOException $e) {
        echo "Chyba při vytváření tabulky '$tableName': " . $e->getMessage();
    }
}

// Vytvoření tabulky uzivatele
createTable($conn, "CREATE TABLE IF NOT EXISTS uzivatele (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                datum_registrace TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )", 'uzivatele');

// Vytvoření tabulky kvizy
createTable($conn, "CREATE TABLE IF NOT EXISTS kvizy (
                kviz_id INT AUTO_INCREMENT PRIMARY KEY,
                nazev VARCHAR(255) NOT NULL,
                popis TEXT
            )", 'kvizy');

// Vytvoření tabulky otazky
createTable($conn, "CREATE TABLE IF NOT EXISTS otazky (
                otazka_id INT AUTO_INCREMENT PRIMARY KEY,
                kviz_id INT,
                otazka_text TEXT NOT NULL,
                FOREIGN KEY (kviz_id) REFERENCES kvizy(kviz_id)
            )", 'otazky');

// Vytvoření tabulky moznosti
createTable($conn, "CREATE TABLE IF NOT EXISTS moznosti (
                moznost_id INT AUTO_INCREMENT PRIMARY KEY,
                otazka_id INT,
                moznost_text TEXT NOT NULL,
                je_spravna BOOLEAN,
                FOREIGN KEY (otazka_id) REFERENCES otazky(otazka_id)
            )", 'moznosti');

// Kontrola, zda již uživatel 'admin' existuje
try {
    $stmt = $conn->prepare("SELECT id FROM uzivatele WHERE username = 'admin'");
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Hash hesla
        $hash = password_hash("admin", PASSWORD_DEFAULT);

        // Vytvoření uživatele 'admin'
        $sql = "INSERT INTO uzivatele (username, password) VALUES ('admin', :hash)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':hash', $hash);
        $stmt->execute();

        echo "Uživatel 'admin' byl úspěšně vytvořen.";
    } else {
        echo "Uživatel 'admin' již existuje.";
    }
} catch (PDOException $e) {
    echo "Chyba při vytváření uživatele 'admin': " . $e->getMessage();
}

$conn = null; // Uzavření připojení k databázi
?>