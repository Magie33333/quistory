<?php
include "./header.php";
// Zde předpokládáme, že 'username' je uloženo v session při přihlášení uživatele
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';

$kvizyInfo = $controller->zobrazKvizyProUzivatele($_SESSION['uzivatel_id'] ?? null);
$odemceneKvizy = $kvizyInfo['odemcene'];
$zamceneKvizy = $kvizyInfo['zamcene'];
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

    <?php
        if (isset($_SESSION['nákup_stav'])) {
            if ($_SESSION['nákup_stav'] == 'úspěšný') {
                echo '<div class="alert alert-success">Nákup kvízu byl úspěšný!</div>';
            } else if ($_SESSION['nákup_stav'] == 'neúspěšný') {
                echo '<div class="alert alert-danger">Nákup kvízu se nezdařil.</div>';
            }
            // Odstranit session proměnnou po zobrazení zprávy
            unset($_SESSION['nákup_stav']);
        }
?>

    <h2>Dostupné kvízy</h2>
    <ul>
    <?php foreach ($odemceneKvizy as $kviz): ?>
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

    <h2>Uzamčené kvízy</h2>
    <ul>
        <?php foreach ($zamceneKvizy as $kviz): ?>
        <li class="zamceny">
            <?php echo htmlspecialchars($kviz['nazev']); ?>
            <i class="fa-solid fa-lock fa-xl"></i>
            <p><?php echo htmlspecialchars($kviz['popis']); ?></p>
            <p>Cena: <?php echo htmlspecialchars($kviz['cena_mozkaky']); ?> <i class="fa-solid fa-coins"></i> Mozkáků</p>

            <form action="../controller/KvizController.php" method="post">
                <input type="hidden" name="action" value="koupitKviz">
                <input type="hidden" name="kviz_id" value="<?php echo $kviz['kviz_id']; ?>">
                <button type="submit" class="koupit-kviz-btn">Koupit</button>
            </form>
        </li>
        <?php endforeach; ?>
    </ul>

    <script src="../scripts/logout.js"></script>
</body>
</html>
