<?php
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
if ($jeAdmin) {
    header('Location: ../index.php');
    exit;
}

include "../controller/KvizController.php";
$controller = new KvizController($conn);
$otazky = $controller->zobrazVsechnyOtazky(); // Předpokládáme, že tato metoda existuje
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava Otázky</title>
    <script>
    function nacistDataOtazky(otazkaId) {
    fetch('../controller/KvizController.php?action=ziskatDetailOtazky&otazka_id=' + otazkaId)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Předpokládáme, že 'data.otazka' obsahuje potřebná data
                document.getElementById('otazka_text').value = data.otazka.otazka_text;
                document.getElementById('kviz_id').value = data.otazka.kviz_id;

                // Zobrazit možnosti
                var moznostiHtml = '';
                data.otazka.moznosti.forEach(function(moznost) {
                    moznostiHtml += '<input type="radio" name="spravna_odpoved" value="' + moznost.moznost_id + '"' + 
                                    (moznost.je_spravna ? ' checked' : '') + '>' +
                                    '<input type="text" name="moznosti[]" value="' + moznost.moznost_text + '"><br>';
                });
                document.getElementById('moznosti').innerHTML = moznostiHtml;
            } else {
                alert('Data otázky se nepodařilo načíst.');
            }
        })
        .catch(error => {
            console.error('Chyba při načítání dat otázky:', error);
        });
}
    </script>
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
            <!-- Možnosti kvízů se načtou zde -->
        </select>
    </div>

    <div id="moznosti">
        <!-- Možnosti otázek se načtou zde -->
    </div>

    <input type="hidden" name="action" value="upravitOtazku">
    <input type="hidden" name="otazka_id" id="hidden_otazka_id">
    <button type="submit">Upravit Otázku</button>
</form>
</body>
</html>