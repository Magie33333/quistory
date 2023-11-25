<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Registrace</title>
    <!-- Link k CSS souboru -->
</head>
<body>
    <h2>Registrace</h2>
    <form action="UzivatelController.php" method="post">
        <div>
            <label for="username">Uživatelské jméno:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Heslo:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <input type="hidden" name="action" value="register">
        <button type="submit">Registrovat</button>
    </form>
    <!-- Zde přidejte PHP kód pro zobrazení chyb -->
</body>
</html>