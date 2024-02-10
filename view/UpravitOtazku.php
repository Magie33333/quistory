<?php
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
if ($jeAdmin) {
    header('Location: ../index.php');
    exit;
}

include "../controller/KvizController.php";
$controller = new KvizController($conn);
$kvizy = $controller->zobrazKvizy();
$otazky = $controller->zobrazVsechnyOtazky(); // Předpokládáme, že tato metoda existuje
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava Otázky</title>
    <link rel="stylesheet" href="../css/sprava.css">
</head>
<body>
    <h1>Upravit Otázku</h1>

    <form id="upravitOtazkuForm" method="post" action="../controller/KvizController.php">
    <div>
        <label for="otazka_id">Vyberte Otázku:</label>
        <select id="otazka_id" name="otazka_id" onchange="nacistDataOtazky(this.value)">
            <?php foreach ($otazky as $otazka): ?>
                <option value="<?php echo $otazka['otazka_id']; ?>">
                    <?php echo htmlspecialchars($otazka['otazka_text']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
    <label for="kviz_id">Kvíz:</label>
    <select id="kviz_id" name="kviz_id">
        <?php foreach ($kvizy as $kviz): ?>
            <option value="<?php echo $kviz['kviz_id']; ?>">
                <?php echo htmlspecialchars($kviz['nazev']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    </div>

    <div>
        <label for="otazka_text">Text Otázky:</label>
        <input type="text" id="otazka_text" name="otazka_text" required>
    </div>

    <div id="moznosti">
        <!-- Možnosti otázek se načtou zde -->
    </div>

    <input type="hidden" name="action" value="upravitOtazku">
    <input type="hidden" name="otazka_id" id="hidden_otazka_id">
    <button type="submit">Upravit otázku</button>
</form>
</body>

<script src="../scripts/upravitOtazku.js"></script>
</html>