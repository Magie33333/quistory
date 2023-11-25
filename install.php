<?php
include 'connect.php'; // Připojení k databázi

try {
    // Vytvoření tabulky uzivatele
    $sql = "CREATE TABLE IF NOT EXISTS uzivatele (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                datum_registrace TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
    $conn->exec($sql);
    echo "Tabulka 'uzivatele' byla úspěšně vytvořena.";

    // Vytvoření tabulky kvizy
    $sql = "CREATE TABLE IF NOT EXISTS kvizy (
                kviz_id INT AUTO_INCREMENT PRIMARY KEY,
                nazev VARCHAR(255) NOT NULL,
                popis TEXT
            )";
    $conn->exec($sql);
    echo "Tabulka 'kvizy' byla úspěšně vytvořena.";

    // Vytvoření tabulky otazky
    $sql = "CREATE TABLE IF NOT EXISTS otazky (
                otazka_id INT AUTO_INCREMENT PRIMARY KEY,
                kviz_id INT,
                otazka_text TEXT NOT NULL,
                FOREIGN KEY (kviz_id) REFERENCES kvizy(kviz_id)
            )";
    $conn->exec($sql);
    echo "Tabulka 'otazky' byla úspěšně vytvořena.";

    $sql = "CREATE TABLE moznosti (
        moznost_id INT AUTO_INCREMENT PRIMARY KEY,
        otazka_id INT,
        moznost_text TEXT NOT NULL,
        je_spravna BOOLEAN,
        FOREIGN KEY (otazka_id) REFERENCES otazky(otazka_id)
    );";
    $conn->exec($sql);
    echo "Tabulka 'moznosti' byla úspěšně vytvořena.";

    // Zde můžete přidat další SQL příkazy pro vytváření dalších tabulek podle potřeby

} catch (PDOException $e) {
    echo "Chyba při vytváření tabulky: " . $e->getMessage();
}

$conn = null; // Uzavření připojení k databázi
?>