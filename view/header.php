<?php
if (!isset($_SESSION['uzivatel_jmeno'])) {
    header('Location: prihlaseni.php'); // Přesměrujeme na přihlašovací stránku, pokud není přihlášen
    exit;
}

$uzivatel_jmeno = $_SESSION['uzivatel_jmeno']; // Získáme jméno přihlášeného uživatele
$mozkaky = $_SESSION['mozkaky'];

?>

<script src="../scripts/header.js"></script>

<head>
    <script src="https://kit.fontawesome.com/395ab71001.js" crossorigin="anonymous"></script>
</head>

<div class="header">
    <div>
        QUISTORY
    </div>
    <div class="user-menu">
        <i class="fa-solid fa-user"></i>
        <?php echo htmlspecialchars($uzivatel_jmeno); ?>
        <span class="mozkaky-stav">
            <i class="fa-solid fa-coins"></i> Mozkáky: <?php echo htmlspecialchars($mozkaky); ?>
        </span>
        <div class="dropdown-menu">
            <a href="#" onclick="odhlasit()">Odhlásit se</a>
    </div>
</div>
</div>