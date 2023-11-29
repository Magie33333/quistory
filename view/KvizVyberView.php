<?php
// Zde předpokládáme, že 'username' je uloženo v session při přihlášení uživatele
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výběr Kvízu</title>
</head>
<body>
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
        </li>
    <?php endforeach; ?>
    </ul>
</body>
</html>
