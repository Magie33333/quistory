<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Vytvoření Kvízu</title>
</head>
<body>
    <h1>Vytvořit Nový Kvíz</h1>

    <form action="../controller/KvizController.php" method="post">
        <input type="hidden" name="action" value="vytvoritKviz">

        <label for="nazev">Název Kvízu:</label>
        <input type="text" id="nazev" name="nazev" required>

        <label for="popis">Popis Kvízu:</label>
        <textarea id="popis" name="popis" required></textarea>

        <button type="submit">Vytvořit Kvíz</button>
    </form>
</body>
</html>