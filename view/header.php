<?php
if (!isset($_SESSION['uzivatel_jmeno'])) {
    header('Location: prihlaseni.php'); // Přesměrujeme na přihlašovací stránku, pokud není přihlášen
    exit;
}

$uzivatel_jmeno = $_SESSION['uzivatel_jmeno']; // Získáme jméno přihlášeného uživatele
$mozkaky = $_SESSION['mozkaky'];

?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var userMenu = document.querySelector('.user-menu');
        var dropdownMenu = document.querySelector('.dropdown-menu');

        userMenu.addEventListener('click', function(event) {
            event.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        window.addEventListener('click', function(event) {
            if (!userMenu.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    });

</script>

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