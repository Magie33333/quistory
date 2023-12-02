<?php
if (!isset($_SESSION['uzivatel_jmeno'])) {
    header('Location: prihlaseni.php'); // Přesměrujeme na přihlašovací stránku, pokud není přihlášen
    exit;
}

$uzivatel_jmeno = $_SESSION['uzivatel_jmeno']; // Získáme jméno přihlášeného uživatele
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

<div class="header">
        <div>QUISTORY</div>
        <div class="user-menu">
            <?php echo htmlspecialchars($uzivatel_jmeno); ?>
            <div class="dropdown-menu">
                <a href="#" onclick="odhlasit()">Odhlásit se</a>
            </div>
        </div>
    </div>