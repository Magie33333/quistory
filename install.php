<?php
include 'connect.php'; // Připojení k databázi

try {
    // SQL příkaz pro vytvoření tabulky uzivatele
    $sql = "CREATE TABLE IF NOT EXISTS uzivatele (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                datum_registrace TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    // Vytvoření tabulky
    $conn->exec($sql);
    echo "Tabulka 'uzivatele' byla úspěšně vytvořena.";

    // Zde můžete přidat další SQL příkazy pro vytváření dalších tabulek podle potřeby

} catch (PDOException $e) {
    echo "Chyba při vytváření tabulky: " . $e->getMessage();
}

$conn = null; // Uzavření připojení k databázi
?>