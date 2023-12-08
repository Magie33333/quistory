<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Vytvoření kvízu</title>
    <link rel="stylesheet" href="../css/sprava.css">
</head>
<body>
    <h1>Vytvořit nový kvíz</h1>

    <form action="../controller/KvizController.php" method="post">
        <input type="hidden" name="action" value="vytvoritKviz">

        <label for="nazev">Název kvízu:</label>
        <input type="text" id="nazev" name="nazev" required>

        <label for="cena">Cena kvízu:</label>
        <input type="text" id="cena" name="cena" required>

        <label for="popis">Popis kvízu:</label>
        <textarea id="popis" name="popis" required></textarea>

        <button type="submit">Vytvořit kvíz</button>
    </form>
</body>
</html>