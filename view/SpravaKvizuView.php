<?php
$jeAdmin = isset($_SESSION['uzivatel_jmeno']) && $_SESSION['uzivatel_jmeno'] == 'admin';
if ($jeAdmin) {
    // Uživatel není admin, přesměrování nebo zobrazení chyby
    header('Location: ../index.php'); // Přesměrujte na hlavní stránku nebo na stránku přihlášení
    exit;
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Správa Kvízů</title>
</head>
<body>
    <h1>Správa Kvízů</h1>

    <a href="./VytvoritKviz.php">Vytvořit Nový Kvíz</a><br>
    <a href="./PridatOtazku.php">Přidat Otázku do Kvízu</a><br>
    <a href="./UpravitKviz.php">Upravit Kvíz</a><br>
    <a href="./UpravitOtazku.php">Upravit otázku</a><br>
</body>
</html>
