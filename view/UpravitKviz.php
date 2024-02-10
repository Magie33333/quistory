<?php
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
if ($jeAdmin) {
    // Uživatel není admin, přesměrování nebo zobrazení chyby
    header('Location: ../index.php'); // Přesměrujte na hlavní stránku nebo na stránku přihlášení
    exit;
}

include "../controller/KvizController.php";
$kvizy = $controller->zobrazKvizy();
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Úprava kvízu</title>
    <link rel="stylesheet" href="../css/sprava.css">

    <script>
    function nacistDetailKvizu(kvizId) {
        fetch('../controller/KvizController.php?action=ziskatDetailKvizu&kviz_id=' + kvizId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('nazev').value = data.kviz.nazev;
                    document.getElementById('popis').value = data.kviz.popis;
                } else {
                    alert('Data kvízu se nepodařilo načíst.');
                }
            })
            .catch(error => {
                console.error('Chyba při načítání dat kvízu:', error);
            });
    }

    </script>

</head>
<body>
    <h1>Upravit kvíz</h1>

    <form action="../controller/KvizController.php" method="post">
        <input type="hidden" name="action" value="upravitKviz">

        <div>
            <label for="kviz_id">Vyberte kvíz:</label>
            <select id="kviz_id" name="kviz_id" required onchange="nacistDetailKvizu(this.value)">
                <?php
                foreach ($kvizy as $kviz) {
                    echo '<option value="' . $kviz['kviz_id'] . '">' . $kviz['nazev'] . '</option>';
                }
                ?>
            </select>
        </div>

        <label for="nazev">Nový název kvízu:</label>
        <input type="text" id="nazev" name="nazev" required>

        <label for="popis">Nový popis kvízu:</label>
        <textarea id="popis" name="popis" required></textarea>

        <button type="submit">Upravit kvíz</button>
        <button type="button" id="smazatKvizBtn">Smazat vybraný kvíz</button>
    </form>
    
<script src="../scripts/upravitKviz.js"></script>

</body>



</html>
