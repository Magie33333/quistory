<?php
include "./header.php";
// Zde předpokládáme, že 'username' je uloženo v session při přihlášení uživatele
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výběr kvízu</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>    

    <h1>Vyberte kvíz</h1>

    
    <?php if ($jeAdmin): ?>
        <div class="admin-panel">
            <a href="SpravaKvizuView.php" class="admin-link">
                <span class="admin-icon">&#9881;</span> <!-- Unicode pro ikonu ozubeného kolečka -->
                Správa kvízů
            </a>
        </div>
    <?php endif; ?>

    <ul>
    <?php foreach ($kvizy as $kviz): ?>
        <li>
            <a href="./KvizProbihaView.php?kviz_id=<?php echo htmlspecialchars($kviz['kviz_id']); ?>">
                <?php echo htmlspecialchars($kviz['nazev']); ?>
                <i class="fa-regular fa-circle-play fa-2xl"></i>
                <p><?php echo htmlspecialchars($kviz['popis']); ?></p>
            </a>
            <div class="high-score-icon-container">
                <a href="./VysledkyView.php?kviz_id=<?php echo htmlspecialchars($kviz['kviz_id']); ?>" title="High Score">
                    <i class="fa-solid fa-clipboard-list fa-2xl"></i>
                </a>
            </div>
        </li>
    <?php endforeach; ?>
    </ul>

    <script src="../conf/logout.js"></script>
</body>
</html>
