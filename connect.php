<?php
    $servername = "localhost";
    $username = "benysekv";
    $password = "KraKEN-17.9.2001";
    $db = "benysekv";

    try {
        // Připojení k DB
        $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Připojení selhalo: " . $e->getMessage();
    }
?>