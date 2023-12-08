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
    <title>Přidání otázky</title>
    <link rel="stylesheet" href="../css/sprava.css">
</head>
<body>
    <h1>Přidat otázku do kvízu</h1>

    <form action="../controller/KvizController.php" method="post">
        <input type="hidden" name="action" value="pridatOtazku">
        
        <div>
            <label for="kviz_id">Vyberte kvíz:</label>
            <select id="kviz_id" name="kviz_id" required>
                <!-- Tady by měly být dynamicky generované možnosti kvízů -->
                <!-- Příklad: -->
                <?php
                foreach ($kvizy as $kviz) {
                    echo '<option value="' . $kviz['kviz_id'] . '">' . $kviz['nazev'] . '</option>';
                }
                ?>
                
            </select>
        </div>

        <div>
            <label for="otazka_text">Text otázky:</label>
            <textarea id="otazka_text" name="otazka_text" required></textarea>
        </div>

        <!-- Pole pro možnosti odpovědí -->
        <div>
            <label for="moznost1">Možnost 1:</label>
            <input type="text" id="moznost1" name="moznost1" required>
            <input type="radio" name="spravna" value="1" required> Správná
        </div>

        <div>
            <label for="moznost2">Možnost 2:</label>
            <input type="text" id="moznost2" name="moznost2" required>
            <input type="radio" name="spravna" value="2"> Správná
        </div>

        <div>
            <label for="moznost3">Možnost 3:</label>
            <input type="text" id="moznost3" name="moznost3" required>
            <input type="radio" name="spravna" value="3"> Správná
        </div>

        <div>
            <label for="moznost4">Možnost 4:</label>
            <input type="text" id="moznost4" name="moznost4" required>
            <input type="radio" name="spravna" value="4"> Správná
        </div>

        <button type="submit">Přidat Otázku</button>
    </form>
</body>
</html>