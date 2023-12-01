<?php
if (!isset($_SESSION['uzivatel_jmeno'])) {
    header('Location: prihlaseni.php'); // Přesměrujeme na přihlašovací stránku, pokud není přihlášen
    exit;
}

$uzivatel_jmeno = $_SESSION['uzivatel_jmeno']; // Získáme jméno přihlášeného uživatele

// Zde předpokládáme, že 'username' je uloženo v session při přihlášení uživatele
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výběr Kvízu</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    var userMenu = document.querySelector('.user-menu');
    var dropdownMenu = document.querySelector('.dropdown-menu');

    // Při kliknutí na uživatelské jméno přepneme třídu 'show'
    userMenu.addEventListener('click', function() {
        dropdownMenu.classList.toggle('show');
    });

    // Zavření dropdown menu při kliknutí mimo menu
    window.addEventListener('click', function(event) {
        if (!userMenu.contains(event.target)) {
            dropdownMenu.classList.remove('show');
        }
    });
});
    </script>
</head>
<body>
    
    <div class="header">
        <div>QUISTORY</div>
        <div class="user-menu">
            <?php echo htmlspecialchars($uzivatel_jmeno); ?>
            <div class="dropdown-menu">
                <a href="#" onclick="odhlasit()">Odhlásit se</a>
            </div>
        </div>
    </div>

    <h1>Vyberte kvíz</h1>

    
    <?php if ($jeAdmin): ?>
        <div>
            <a href="SpravaKvizuView.php">Správa kvízů</a>
        </div>
    <?php endif; ?>

    <ul>
    <?php foreach ($kvizy as $kviz): ?>
        <li>
            <a href="./KvizProbihaView.php?kviz_id=<?php echo htmlspecialchars($kviz['kviz_id']); ?>">
                <?php echo htmlspecialchars($kviz['nazev']); ?>
            </a>
            <p><?php echo htmlspecialchars($kviz['popis']); ?></p>
        </li>
    <?php endforeach; ?>
    </ul>

    <script src="../conf/logout.js"></script>
</body>
</html>
