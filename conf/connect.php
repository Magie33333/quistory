<?php
    $servername = "localhost";
    $username = "root";
    $password = "benysek...";
    $db = "quistory";

    try {
        // Připojení k DB
        $conn = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Připojení selhalo: " . $e->getMessage();
    }
?>